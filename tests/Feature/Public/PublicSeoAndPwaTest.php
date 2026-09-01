<?php

namespace Tests\Feature\Public;

use App\Core\Business\Models\Business;
use App\Core\Cms\Actions\CreatePage;
use App\Core\Cms\Actions\PublishPage;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class PublicSeoAndPwaTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_sitemap_contains_all_three_locales_only_for_published_pages(): void
    {
        $business = Business::factory()->create(['slug' => 'denardi']);
        $actor = User::factory()->for($business)->create();
        $published = app(CreatePage::class)->execute($business, $actor, 'about', 'standard', $this->readyTranslations('About'));
        app(PublishPage::class)->execute($published, $actor, 0);
        app(CreatePage::class)->execute($business, $actor, 'privacy', 'standard', $this->readyTranslations('Privacy'));

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee(url('/fa/about'))
            ->assertSee(url('/en/about'))
            ->assertSee(url('/ar/about'))
            ->assertSee('hreflang="x-default"', false)
            ->assertDontSee(url('/fa/privacy'));
    }

    public function test_public_shell_exposes_manifest_offline_fallback_and_dynamic_robots(): void
    {
        $this->get('/offline')->assertOk()->assertSee('Internet connection is unavailable.');
        $this->get('/robots.txt')
            ->assertOk()
            ->assertSee('Disallow: /admin')
            ->assertSee(route('public.sitemap'));
        $this->assertFileExists(public_path('manifest.webmanifest'));
        $this->assertFileExists(public_path('admin.webmanifest'));
        $this->assertFileExists(public_path('sw.js'));
        $this->assertFileExists(public_path('denardi-icon.svg'));
    }

    /** @return array<string, array<string, string>> */
    private function readyTranslations(string $title): array
    {
        return [
            'fa' => ['title' => $title.' FA', 'translation_state' => 'ready'],
            'en' => ['title' => $title.' EN', 'translation_state' => 'ready'],
            'ar' => ['title' => $title.' AR', 'translation_state' => 'ready'],
        ];
    }
}
