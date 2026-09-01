<?php

namespace Tests\Feature\Console;

use App\Core\Cms\Models\Page;
use App\Core\Menu\Models\MenuCategory;
use App\Core\Menu\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ProvisionDenardiDevelopmentTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_local_provisioning_is_idempotent_and_creates_three_language_public_content(): void
    {
        $this->app->detectEnvironment(fn (): string => 'local');
        User::factory()->godfather()->create();

        try {
            $this->artisan('denardi:provision-development')->assertSuccessful();
            $this->artisan('denardi:provision-development')->assertSuccessful();

            $this->assertSame(4, Page::query()->count());
            $this->assertSame(12, Page::query()->withCount('translations')->get()->sum('translations_count'));
            $this->assertSame(4, MenuCategory::query()->count());
            $this->assertSame(9, Product::query()->count());
            $this->assertSame(27, Product::query()->withCount('translations')->get()->sum('translations_count'));
            $this->withoutVite();
            $this->get('/fa')->assertOk()->assertSee('دناردی؛ هنر، قهوه و آبمیوه');
            $this->get('/ar/menu')->assertOk()->assertSee('إسبريسو')->assertSee('غير متوفر');
            $this->get('/en/menu')->assertOk()->assertSee('Butter Croissant')->assertDontSee('Seasonal Mix');
        } finally {
            $this->app->detectEnvironment(fn (): string => 'testing');
        }
    }
}
