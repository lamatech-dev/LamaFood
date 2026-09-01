<?php

namespace Tests\Feature\Api\Admin\V1\Media;

use App\Core\Business\Models\Business;
use App\Core\Media\Models\Media;
use App\Core\Menu\Actions\CreateMenuCategory;
use App\Core\Menu\Actions\CreateProduct;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MediaManagementControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_upload_preserves_original_and_generates_webp_and_thumbnail(): void
    {
        Storage::fake('public');
        $business = Business::factory()->create(['slug' => 'denardi']);
        Sanctum::actingAs(User::factory()->godfather()->create());

        $response = $this->post('/api/admin/v1/media', [
            'file' => UploadedFile::fake()->image('coffee.jpg', 1200, 800),
            'translations' => $this->translations(),
        ], ['Accept' => 'application/json']);

        $response->assertCreated()
            ->assertJsonFragment(['locale' => 'fa', 'alt' => 'تصویر قهوه'])
            ->assertJsonFragment(['locale' => 'en', 'alt' => 'Coffee image'])
            ->assertJsonFragment(['locale' => 'ar', 'alt' => 'صورة القهوة']);
        $media = $response->json('data');
        Storage::disk('public')->assertExists($media['path']);
        Storage::disk('public')->assertExists($media['optimized_path']);
        Storage::disk('public')->assertExists($media['thumbnail_path']);
        $this->assertStringStartsWith('RIFF', Storage::disk('public')->get($media['optimized_path']));
        $this->assertStringContainsString('WEBP', substr(Storage::disk('public')->get($media['thumbnail_path']), 0, 16));
        $this->assertDatabaseHas('media_translations', ['locale' => 'ar', 'alt' => 'صورة القهوة']);
    }

    public function test_listing_reports_product_usage_and_prevents_in_use_deletion(): void
    {
        Storage::fake('public');
        $business = Business::factory()->create(['slug' => 'denardi']);
        $actor = User::factory()->godfather()->create();
        Sanctum::actingAs($actor);
        $media = $this->upload();
        $category = app(CreateMenuCategory::class)->execute($business, $actor, 'coffee', 0, $this->menuTranslations());
        app(CreateProduct::class)->execute($category, $actor, 'espresso', 0, $this->menuTranslations(), primaryMedia: $media);

        $this->getJson('/api/admin/v1/media')
            ->assertOk()
            ->assertJsonPath('data.data.0.products_count', 1)
            ->assertJsonPath('data.data.0.usages_count', 0)
            ->assertJsonPath('data.data.0.products.0.slug', 'espresso');
        $this->deleteJson("/api/admin/v1/media/{$media->public_id}")->assertConflict();

        $this->assertModelExists($media);
    }

    public function test_delete_removes_original_and_all_derivatives_when_unused(): void
    {
        Storage::fake('public');
        Business::factory()->create(['slug' => 'denardi']);
        Sanctum::actingAs(User::factory()->godfather()->create());
        $media = $this->upload();
        $paths = [$media->path, $media->optimized_path, $media->thumbnail_path];

        $this->deleteJson("/api/admin/v1/media/{$media->public_id}")->assertNoContent();

        Storage::disk('public')->assertMissing($paths);
        $this->assertDatabaseMissing('media', ['id' => $media->id]);
    }

    private function upload(): Media
    {
        $response = $this->post('/api/admin/v1/media', [
            'file' => UploadedFile::fake()->image('coffee.png', 900, 600),
            'translations' => $this->translations(),
        ], ['Accept' => 'application/json'])->assertCreated();

        return Media::query()->where('public_id', $response->json('data.public_id'))->firstOrFail();
    }

    /** @return array<string, array<string, string>> */
    private function translations(): array
    {
        return [
            'fa' => ['alt' => 'تصویر قهوه', 'title' => 'قهوه'],
            'en' => ['alt' => 'Coffee image', 'title' => 'Coffee'],
            'ar' => ['alt' => 'صورة القهوة', 'title' => 'قهوة'],
        ];
    }

    /** @return array<string, array<string, string>> */
    private function menuTranslations(): array
    {
        return [
            'fa' => ['name' => 'قهوه', 'translation_state' => 'ready'],
            'en' => ['name' => 'Coffee', 'translation_state' => 'ready'],
            'ar' => ['name' => 'قهوة', 'translation_state' => 'ready'],
        ];
    }
}
