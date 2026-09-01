<?php

namespace Tests\Feature\Public;

use App\Core\Business\Models\Business;
use App\Core\Cms\Actions\CreateBlock;
use App\Core\Cms\Actions\CreatePage;
use App\Core\Cms\Actions\PublishPage;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class LocalizedPublicPagesTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_public_page_renders_each_locale_with_metadata_direction_and_no_fallback_mixing(): void
    {
        $this->publishHome();

        $this->get('/fa')->assertOk()->assertSee('dir="rtl"', false)->assertSee('دناردی')->assertDontSee('ديناردي');
        $this->get('/en')->assertOk()->assertSee('dir="ltr"', false)->assertSee('Denardi is')->assertDontSee('دناردی');
        $this->get('/ar')->assertOk()->assertSee('dir="rtl"', false)->assertSee('ديناردي')->assertDontSee('دناردی');
    }

    public function test_root_uses_a_supported_browser_language(): void
    {
        $this->get('/', ['Accept-Language' => 'ar,en;q=0.8'])->assertRedirect('/ar');
    }

    private function publishHome(): void
    {
        $business = Business::factory()->create(['slug' => 'denardi']);
        $actor = User::factory()->for($business)->create();
        $page = app(CreatePage::class)->execute($business, $actor, 'home', 'landing', [
            'fa' => ['title' => 'خانه', 'meta_title' => 'دناردی', 'translation_state' => 'ready'],
            'en' => ['title' => 'Home', 'meta_title' => 'Denardi', 'translation_state' => 'ready'],
            'ar' => ['title' => 'الرئيسية', 'meta_title' => 'ديناردي', 'translation_state' => 'ready'],
        ]);
        app(CreateBlock::class)->execute($page, $actor, 'hero', 0, ['alignment' => 'center'], [
            'fa' => ['content' => ['title' => 'دناردی اینجاست'], 'translation_state' => 'ready'],
            'en' => ['content' => ['title' => 'Denardi is here'], 'translation_state' => 'ready'],
            'ar' => ['content' => ['title' => 'ديناردي هنا'], 'translation_state' => 'ready'],
        ]);
        app(PublishPage::class)->execute($page->fresh(), $actor, 1);
    }
}
