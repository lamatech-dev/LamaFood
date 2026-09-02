<?php

namespace Tests\Feature\Public;

use App\Core\Business\Models\Business;
use App\Core\Cms\Actions\CreateBlock;
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
            ->assertSee(url('/about'))
            ->assertSee(url('/en/about'))
            ->assertSee(url('/ar/about'))
            ->assertSee('hreflang="x-default"', false)
            ->assertDontSee(url('/privacy'));
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
        $this->assertFileExists(public_path('icons/icon-192.png'));
        $this->assertFileExists(public_path('icons/icon-512.png'));
        $this->assertFileExists(public_path('icons/maskable-512.png'));
        $this->assertFileExists(public_path('icons/apple-touch-icon.png'));

        $manifest = json_decode((string) file_get_contents(public_path('manifest.webmanifest')), true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame('standalone', $manifest['display']);
        $this->assertSame('/', $manifest['start_url']);
        $this->assertContains(['src' => '/icons/icon-192.png', 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'], $manifest['icons']);
        $this->assertContains(['src' => '/icons/icon-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'], $manifest['icons']);
        $this->assertContains(['src' => '/icons/maskable-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'], $manifest['icons']);
        $this->assertSame([192, 192], array_slice(getimagesize(public_path('icons/icon-192.png')), 0, 2));
        $this->assertSame([512, 512], array_slice(getimagesize(public_path('icons/icon-512.png')), 0, 2));
        $this->assertSame([512, 512], array_slice(getimagesize(public_path('icons/maskable-512.png')), 0, 2));

        $serviceWorker = (string) file_get_contents(public_path('sw.js'));
        $this->assertStringContainsString("url.pathname.startsWith('/api/')", $serviceWorker);
        $this->assertStringNotContainsString("addEventListener('sync'", $serviceWorker);
    }

    public function test_published_contact_content_drives_local_business_structured_data(): void
    {
        $this->withoutVite();
        config()->set('denardi.phone');
        config()->set('denardi.map_url');
        config()->set('denardi.instagram_url');
        config()->set('denardi.schema.address');

        $business = Business::factory()->create(['name' => 'Verified Denardi', 'slug' => 'denardi']);
        $actor = User::factory()->for($business)->create();
        $page = app(CreatePage::class)->execute($business, $actor, 'contact', 'standard', $this->readyTranslations('Contact'));
        app(CreateBlock::class)->execute($page, $actor, 'location', 0, [
            'lat' => 35.7219,
            'lng' => 51.3347,
            'mapUrl' => 'https://maps.example.test/verified',
        ], $this->locationTranslations());
        app(CreateBlock::class)->execute($page->fresh(), $actor, 'contact', 1, [
            'phone' => '+982100000000',
            'instagramUrl' => 'https://www.instagram.com/verified-denardi',
        ], $this->contactTranslations());
        app(PublishPage::class)->execute($page->fresh(), $actor, 2);

        $response = $this->get('/ar/contact')->assertOk()
            ->assertSee('hreflang="fa"', false)
            ->assertSee('hreflang="en"', false)
            ->assertSee('hreflang="ar"', false)
            ->assertSee('hreflang="x-default"', false);

        preg_match('/<script type="application\/ld\+json">(.*?)<\/script>/s', (string) $response->getContent(), $matches);
        $this->assertArrayHasKey(1, $matches);
        $data = json_decode($matches[1], true, flags: JSON_THROW_ON_ERROR);

        $this->assertSame('https://schema.org', $data['@context']);
        $this->assertSame('CafeOrCoffeeShop', $data['@type']);
        $this->assertSame('Verified Denardi', $data['name']);
        $this->assertSame('ar', $data['inLanguage']);
        $this->assertSame('العنوان المؤكد', $data['address']['streetAddress']);
        $this->assertSame(35.7219, $data['geo']['latitude']);
        $this->assertSame(51.3347, $data['geo']['longitude']);
        $this->assertSame('+982100000000', $data['telephone']);
        $this->assertSame(['https://www.instagram.com/verified-denardi'], $data['sameAs']);
        $this->assertSame(url('/ar/menu'), $data['menu']);
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

    /** @return array<string, array<string, mixed>> */
    private function locationTranslations(): array
    {
        return [
            'fa' => ['content' => ['heading' => 'نشانی', 'address' => 'نشانی تأییدشده'], 'translation_state' => 'ready'],
            'en' => ['content' => ['heading' => 'Address', 'address' => 'Verified address'], 'translation_state' => 'ready'],
            'ar' => ['content' => ['heading' => 'العنوان', 'address' => 'العنوان المؤكد'], 'translation_state' => 'ready'],
        ];
    }

    /** @return array<string, array<string, mixed>> */
    private function contactTranslations(): array
    {
        return [
            'fa' => ['content' => ['heading' => 'تماس'], 'translation_state' => 'ready'],
            'en' => ['content' => ['heading' => 'Contact'], 'translation_state' => 'ready'],
            'ar' => ['content' => ['heading' => 'اتصال'], 'translation_state' => 'ready'],
        ];
    }
}
