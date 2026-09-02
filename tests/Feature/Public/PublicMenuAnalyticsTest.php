<?php

namespace Tests\Feature\Public;

use App\Core\Analytics\AnalyticsEventType;
use App\Core\Analytics\Models\AnalyticsEvent;
use App\Core\Business\Models\Branch;
use App\Core\Business\Models\Business;
use App\Core\Menu\Actions\ChangeCategoryPublicationState;
use App\Core\Menu\Actions\ChangeProductPublicationState;
use App\Core\Menu\Actions\CreateMenuCategory;
use App\Core\Menu\Actions\CreateProduct;
use App\Core\Menu\Actions\UpdateProductBranchSetting;
use App\Core\Menu\AvailabilityState;
use App\Core\Menu\Models\MenuCategory;
use App\Core\Menu\Models\Product;
use App\Core\Menu\PublicationState;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PublicMenuAnalyticsTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_records_menu_category_and_product_views_once_per_visitor_window_without_raw_ip(): void
    {
        $this->withoutVite();
        $this->withCredentials();
        [$branch, $category, $product] = $this->publishedMenu();
        $visitor = str_repeat('v', 40);

        $this->withCookie('denardi_visitor', $visitor)
            ->withHeader('User-Agent', 'Mobile Safari')
            ->get('/ar/menu')
            ->assertOk()
            ->assertSee('data-analytics-type="category_view"', false)
            ->assertSee('data-analytics-type="product_view"', false);

        $categoryPayload = ['type' => 'category_view', 'subject_public_id' => $category->public_id, 'locale' => 'ar', 'branch' => $branch->slug];
        $productPayload = ['type' => 'product_view', 'subject_public_id' => $product->public_id, 'locale' => 'ar', 'branch' => $branch->slug];
        $this->withCookie('denardi_visitor', $visitor)->postJson('/analytics/views', $categoryPayload)->assertNoContent();
        $categoryEvent = AnalyticsEvent::query()->where('event_type', AnalyticsEventType::CategoryView)->sole();
        $this->assertSame(hash_hmac('sha256', $visitor, (string) config('app.key')), $categoryEvent->visitor_hash);
        $this->withCookie('denardi_visitor', $visitor)->postJson('/analytics/views', $categoryPayload)->assertNoContent();
        $this->assertSame(1, AnalyticsEvent::query()->where('event_type', AnalyticsEventType::CategoryView)->count());
        $this->withCookie('denardi_visitor', $visitor)->postJson('/analytics/views', $productPayload)->assertNoContent();
        $this->withCookie('denardi_visitor', $visitor)->postJson('/analytics/views', $productPayload)->assertNoContent();

        $this->assertDatabaseCount('analytics_events', 3);
        $events = AnalyticsEvent::query()->orderBy('id')->get();
        $this->assertSame(['menu_view', 'category_view', 'product_view'], $events->pluck('event_type')->map->value->all());
        $this->assertSame([$branch->id], $events->pluck('branch_id')->unique()->values()->all());
        $this->assertSame(['ar'], $events->pluck('locale')->unique()->values()->all());
        $this->assertSame(64, mb_strlen($events->firstOrFail()->visitor_hash));
        $this->assertArrayNotHasKey('ip_address', $events->firstOrFail()->getAttributes());
        $this->assertSame($category->public_id, $events->firstWhere('event_type', AnalyticsEventType::CategoryView)?->subject_public_id);
        $this->assertSame($product->public_id, $events->firstWhere('event_type', AnalyticsEventType::ProductView)?->subject_public_id);
    }

    public function test_bot_views_and_non_public_subjects_are_not_recorded(): void
    {
        $this->withCredentials();
        [$branch, $category] = $this->publishedMenu();
        $visitor = str_repeat('b', 40);
        $payload = ['type' => 'category_view', 'subject_public_id' => $category->public_id, 'locale' => 'fa', 'branch' => $branch->slug];

        $this->withCookie('denardi_visitor', $visitor)
            ->withHeader('User-Agent', 'ExampleBot/1.0')
            ->postJson('/analytics/views', $payload)
            ->assertNoContent();
        $this->withCookie('denardi_visitor', $visitor)
            ->postJson('/analytics/views', [...$payload, 'subject_public_id' => (string) Str::ulid()])
            ->assertNotFound();

        $this->assertDatabaseCount('analytics_events', 0);
    }

    public function test_rejects_unsupported_analytics_types_and_locales(): void
    {
        [$branch, $category] = $this->publishedMenu();

        $this->postJson('/analytics/views', [
            'type' => 'page_view',
            'subject_public_id' => $category->public_id,
            'locale' => 'de',
            'branch' => $branch->slug,
        ])->assertUnprocessable()->assertJsonValidationErrors(['type', 'locale']);
    }

    /** @return array{Branch, MenuCategory, Product} */
    private function publishedMenu(): array
    {
        $business = Business::factory()->create(['slug' => 'denardi']);
        $branch = Branch::factory()->for($business)->create(['slug' => 'central', 'is_default' => true]);
        $actor = User::factory()->for($business)->create();
        $translations = [
            'fa' => ['name' => 'قهوه', 'translation_state' => 'ready'],
            'en' => ['name' => 'Coffee', 'translation_state' => 'ready'],
            'ar' => ['name' => 'قهوة', 'translation_state' => 'ready'],
        ];
        $category = app(CreateMenuCategory::class)->execute($business, $actor, 'coffee', 0, $translations);
        app(ChangeCategoryPublicationState::class)->execute($category, $actor, PublicationState::Published);
        $product = app(CreateProduct::class)->execute($category, $actor, 'latte', 0, $translations);
        app(ChangeProductPublicationState::class)->execute($product, $actor, PublicationState::Published);
        app(UpdateProductBranchSetting::class)->execute($product, $branch, $actor, 2_500_000, AvailabilityState::Available, 0);

        return [$branch, $category, $product];
    }
}
