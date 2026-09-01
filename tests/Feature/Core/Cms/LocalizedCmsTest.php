<?php

namespace Tests\Feature\Core\Cms;

use App\Core\Business\Models\Business;
use App\Core\Cms\Actions\CreateBlock;
use App\Core\Cms\Actions\CreatePage;
use App\Core\Cms\Actions\PublishPage;
use App\Core\Cms\BlockPayloadValidator;
use App\Core\Cms\PageReadiness;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Validation\ValidationException;
use LogicException;
use Tests\TestCase;

class LocalizedCmsTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_block_schema_rejects_locale_keys_and_unknown_structure_fields(): void
    {
        $this->expectException(ValidationException::class);

        app(BlockPayloadValidator::class)->validateStructure('hero', [
            'mediaId' => 10,
            'fa' => ['title' => 'نباید اینجا باشد'],
        ]);
    }

    public function test_draft_content_can_be_incomplete_but_ready_content_must_match_schema(): void
    {
        $validator = app(BlockPayloadValidator::class);
        $validator->validateContent('hero', [], false);

        $this->expectException(ValidationException::class);
        $validator->validateContent('hero', [], true);
    }

    public function test_page_and_block_store_three_independent_translation_rows_and_publish_snapshot(): void
    {
        $business = Business::factory()->create();
        $actor = User::factory()->for($business)->create();
        $pageTranslations = [
            'fa' => ['title' => 'خانه', 'translation_state' => 'ready'],
            'en' => ['title' => 'Home', 'translation_state' => 'ready'],
            'ar' => ['title' => 'الرئيسية', 'translation_state' => 'ready'],
        ];
        $page = app(CreatePage::class)->execute($business, $actor, 'home', 'landing', $pageTranslations);
        $blockTranslations = [
            'fa' => ['content' => ['title' => 'دناردی'], 'translation_state' => 'ready'],
            'en' => ['content' => ['title' => 'Denardi'], 'translation_state' => 'ready'],
            'ar' => ['content' => ['title' => 'ديناردي'], 'translation_state' => 'ready'],
        ];
        $block = app(CreateBlock::class)->execute($page, $actor, 'hero', 0, ['alignment' => 'center'], $blockTranslations);

        $this->assertSame(['ar', 'en', 'fa'], $page->translations->pluck('locale')->sort()->values()->all());
        $this->assertSame(['ar', 'en', 'fa'], $block->translations->pluck('locale')->sort()->values()->all());
        $this->assertTrue(app(PageReadiness::class)->report($page->fresh())['ready']);

        $revision = app(PublishPage::class)->execute($page->fresh(), $actor, 1);
        $this->assertSame('Denardi', $revision->snapshot_json['blocks'][0]['translations']['en']['content_json']['title']);

        $this->expectException(LogicException::class);
        $revision->update(['snapshot_json' => []]);
    }

    public function test_readiness_does_not_fallback_or_silently_mix_missing_locales(): void
    {
        $business = Business::factory()->create();
        $actor = User::factory()->for($business)->create();
        $page = app(CreatePage::class)->execute($business, $actor, 'about', 'standard', [
            'fa' => ['title' => 'درباره', 'translation_state' => 'ready'],
        ]);

        $report = app(PageReadiness::class)->report($page);

        $this->assertFalse($report['ready']);
        $this->assertTrue($report['locales']['fa']['ready']);
        $this->assertFalse($report['locales']['en']['ready']);
        $this->assertFalse($report['locales']['ar']['ready']);
    }
}
