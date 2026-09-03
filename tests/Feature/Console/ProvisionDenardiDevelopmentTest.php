<?php

namespace Tests\Feature\Console;

use App\Core\Cms\Models\Page;
use App\Core\Media\Models\Media;
use App\Core\Menu\Models\MenuCategory;
use App\Core\Menu\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProvisionDenardiDevelopmentTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_local_provisioning_is_idempotent_and_creates_three_language_public_content(): void
    {
        Storage::fake('public');
        Storage::fake('local');
        $this->app->detectEnvironment(fn (): string => 'local');
        User::factory()->godfather()->create();

        try {
            $this->artisan('denardi:provision-development')->assertSuccessful();
            $originalMediaIds = Media::query()->pluck('public_id')->all();
            $this->artisan('denardi:provision-development')->assertSuccessful();
            $this->assertSame($originalMediaIds, Media::query()->pluck('public_id')->all());
            Storage::disk('public')->assertExists('demo/denardi/coffee.webp');

            $this->assertSame(4, Page::query()->count());
            $this->assertSame(12, Page::query()->withCount('translations')->get()->sum('translations_count'));
            $this->assertSame(8, MenuCategory::query()->count());
            $this->assertSame(24, Product::query()->count());
            $this->assertSame(72, Product::query()->withCount('translations')->get()->sum('translations_count'));
            $this->withoutVite();
            $this->get('/')->assertOk()->assertSee('دناردی؛ مکثی برای طعم و هنر');
            $this->get('/about')->assertOk()->assertSee('درباره دناردی');
            $this->get('/contact')->assertOk()->assertSee('ارتباط با دناردی');
            $this->get('/menu')->assertSee('<small>تومان</small>', false);
            $this->get('/ar/menu')->assertOk()->assertSee('إسبريسو')->assertSee('غير متوفر')->assertSee('<small>T</small>', false);
            $this->get('/en/menu')->assertOk()->assertSee('Butter Croissant')->assertDontSee('Fig Latte')->assertDontSee('Winter Cocoa')->assertSee('<small>T</small>', false);
            $this->assertGreaterThan(0, Product::query()->whereNull('primary_media_id')->count());
            $this->assertGreaterThan(0, Product::query()->whereNotNull('primary_media_id')->count());
            $this->get('/en/contact')->assertOk()->assertSee('Demo address')->assertDontSee('href="tel:+00', false);
        } finally {
            $this->app->detectEnvironment(fn (): string => 'testing');
        }
    }

    public function test_demo_reset_and_removal_preserve_unowned_content_and_shared_categories(): void
    {
        Storage::fake('public');
        Storage::fake('local');
        $this->app->detectEnvironment(fn (): string => 'local');
        User::factory()->godfather()->create();

        try {
            $this->artisan('denardi:provision-development')->assertSuccessful();
            $category = MenuCategory::query()->firstOrFail();
            $customProduct = Product::query()->create([
                'public_id' => (string) Str::ulid(), 'business_id' => $category->business_id,
                'category_id' => $category->id, 'slug' => 'customer-owned-product',
            ]);
            $customPage = Page::query()->create([
                'public_id' => (string) Str::ulid(), 'business_id' => $category->business_id, 'slug' => 'customer-owned-page',
            ]);
            $this->artisan('denardi:provision-development', ['--reset' => true])->assertSuccessful();
            $this->assertSame(25, Product::query()->count());
            $this->assertModelExists($customProduct);
            $this->artisan('denardi:provision-development', ['--remove' => true])->assertSuccessful();
            $this->assertSame(1, Product::query()->count());
            $this->assertSame(1, Page::query()->count());
            $this->assertModelExists($customProduct);
            $this->assertModelExists($customPage);
            $this->assertModelExists($category);
        } finally {
            $this->app->detectEnvironment(fn (): string => 'testing');
        }
    }

    public function test_demo_provisioning_is_rejected_outside_local_environment(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Development content can only be provisioned in the local environment.');
        $this->artisan('denardi:provision-development', ['--reset' => true]);
    }
}
