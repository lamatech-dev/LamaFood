<?php

namespace Tests\Feature\Api\Admin\V1\Cms;

use App\Core\Authorization\FoundationRole;
use App\Core\Authorization\ProvisionFoundationRbac;
use App\Core\Business\Models\Business;
use App\Core\Cms\Actions\CreateBlock;
use App\Core\Cms\Actions\CreatePage;
use App\Core\Cms\Actions\PublishPage;
use App\Core\Cms\Models\Page;
use App\Core\Cms\PageStatus;
use App\Core\Media\MediaStatus;
use App\Core\Media\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class CmsManagementControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_updates_page_and_reports_unpublished_changes(): void
    {
        [$page, $actor] = $this->page('about');
        Sanctum::actingAs($actor);

        $this->putJson("/api/admin/v1/cms/pages/{$page->public_id}", [
            'slug' => 'our-story',
            'template' => 'standard',
            'translations' => $this->pageTranslations('داستان ما', 'Our story', 'قصتنا'),
        ])->assertOk()
            ->assertJsonPath('data.slug', 'our-story')
            ->assertJsonPath('data.has_unpublished_changes', true)
            ->assertJsonPath('data.readiness.ready', true);

        $this->assertDatabaseHas('page_translations', ['page_id' => $page->id, 'locale' => 'ar', 'title' => 'قصتنا']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'cms.page.updated']);
    }

    public function test_updates_reorders_disables_and_deletes_blocks(): void
    {
        [$page, $actor] = $this->page('home');
        $first = app(CreateBlock::class)->execute($page, $actor, 'hero', 0, [], $this->blockTranslations('اول', 'First', 'الأول'));
        $second = app(CreateBlock::class)->execute($page->fresh(), $actor, 'hero', 1, [], $this->blockTranslations('دوم', 'Second', 'الثاني'));
        Sanctum::actingAs($actor);

        $this->putJson("/api/admin/v1/cms/pages/{$page->public_id}/blocks/{$first->public_id}", [
            'type' => 'hero',
            'is_enabled' => false,
            'structure' => ['alignment' => 'center'],
            'translations' => $this->blockTranslations('ویرایش', 'Edited', 'معدل'),
        ])->assertOk()->assertJsonPath('data.is_enabled', false);
        $this->putJson("/api/admin/v1/cms/pages/{$page->public_id}/blocks/order", [
            'blocks' => [$second->public_id, $first->public_id],
        ])->assertOk()->assertJsonPath('data.blocks.0.public_id', $second->public_id);
        $this->deleteJson("/api/admin/v1/cms/pages/{$page->public_id}/blocks/{$second->public_id}")->assertNoContent();

        $this->assertDatabaseMissing('blocks', ['id' => $second->id]);
        $this->assertSame(0, $first->fresh()->position);
        $this->assertDatabaseHas('audit_logs', ['action' => 'cms.block.updated']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'cms.blocks.reordered']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'cms.block.deleted']);
    }

    public function test_preview_shows_current_draft_but_publish_snapshot_excludes_disabled_blocks(): void
    {
        $this->withoutVite();
        [$page, $actor] = $this->page('contact');
        $media = Media::query()->create([
            'public_id' => (string) Str::ulid(),
            'business_id' => $page->business_id,
            'disk' => 'public',
            'path' => 'media/original.jpg',
            'optimized_path' => 'media/optimized.webp',
            'thumbnail_path' => 'media/thumbnail.webp',
            'mime' => 'image/jpeg',
            'size' => 100,
            'checksum' => str_repeat('a', 64),
            'status' => MediaStatus::Ready,
            'uploaded_by' => $actor->id,
        ]);
        $enabled = app(CreateBlock::class)->execute($page, $actor, 'hero', 0, ['mediaId' => $media->id], $this->blockTranslations('فعال', 'Visible draft', 'ظاهر'));
        $disabled = app(CreateBlock::class)->execute($page->fresh(), $actor, 'hero', 1, [], $this->blockTranslations('خاموش', 'Hidden draft', 'مخفي'));
        $disabled->update(['is_enabled' => false]);
        Sanctum::actingAs($actor);

        $this->get("/api/admin/v1/cms/pages/{$page->public_id}/preview/en")
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertSee('Visible draft')
            ->assertDontSee('Hidden draft')
            ->assertSee('noindex,nofollow', false);
        $revision = app(PublishPage::class)->execute($page->fresh(), $actor, 2);

        $this->assertCount(1, $revision->snapshot_json['blocks']);
        $this->assertSame($enabled->public_id, $revision->snapshot_json['blocks'][0]['public_id']);
        $this->assertSame('media/optimized.webp', $revision->snapshot_json['blocks'][0]['media'][$media->id]['optimized_path']);
    }

    public function test_delete_archives_published_page_and_hard_deletes_unpublished_page(): void
    {
        [$published, $actor] = $this->page('about');
        app(PublishPage::class)->execute($published, $actor, 0);
        [$draft] = $this->page('privacy', $published->business, $actor);
        Sanctum::actingAs($actor);

        $this->deleteJson("/api/admin/v1/cms/pages/{$published->public_id}")
            ->assertOk()->assertJsonPath('data.result', 'archived');
        $this->deleteJson("/api/admin/v1/cms/pages/{$draft->public_id}")
            ->assertOk()->assertJsonPath('data.result', 'deleted');

        $this->assertSame(PageStatus::Archived, $published->fresh()->status);
        $this->assertDatabaseMissing('pages', ['id' => $draft->id]);
    }

    public function test_returns_404_when_business_user_edits_another_business_page(): void
    {
        [$page] = $this->page('about');
        $otherBusiness = Business::factory()->create();
        $user = User::factory()->for($otherBusiness)->create();
        app(ProvisionFoundationRbac::class)->execute($otherBusiness);
        app(PermissionRegistrar::class)->setPermissionsTeamId($otherBusiness->id);
        $user->assignRole(FoundationRole::BusinessOwner->value);
        Sanctum::actingAs($user);

        $this->putJson("/api/admin/v1/cms/pages/{$page->public_id}", [
            'slug' => 'other',
            'translations' => $this->pageTranslations('الف', 'B', 'ج'),
        ])->assertNotFound();
    }

    /** @return array{Page, User} */
    private function page(string $slug, ?Business $business = null, ?User $actor = null): array
    {
        $business ??= Business::factory()->create();
        $actor ??= User::factory()->godfather()->create();
        $page = app(CreatePage::class)->execute($business, $actor, $slug, 'standard', $this->pageTranslations('فارسی', 'English', 'العربية'));

        return [$page, $actor];
    }

    /** @return array<string, array<string, string>> */
    private function pageTranslations(string $fa, string $en, string $ar): array
    {
        return [
            'fa' => ['title' => $fa, 'translation_state' => 'ready'],
            'en' => ['title' => $en, 'translation_state' => 'ready'],
            'ar' => ['title' => $ar, 'translation_state' => 'ready'],
        ];
    }

    /** @return array<string, array<string, mixed>> */
    private function blockTranslations(string $fa, string $en, string $ar): array
    {
        return [
            'fa' => ['content' => ['title' => $fa], 'translation_state' => 'ready'],
            'en' => ['content' => ['title' => $en], 'translation_state' => 'ready'],
            'ar' => ['content' => ['title' => $ar], 'translation_state' => 'ready'],
        ];
    }
}
