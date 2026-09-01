<?php

namespace Tests\Feature\Api\Admin\V1\Analytics;

use App\Core\Analytics\AnalyticsEventType;
use App\Core\Analytics\Models\AnalyticsEvent;
use App\Core\Business\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AnalyticsSummaryControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_returns_401_when_unauthenticated(): void
    {
        $this->getJson('/api/admin/v1/analytics/summary')->assertUnauthorized();
    }

    public function test_godfather_summary_counts_events_by_period_and_type(): void
    {
        $business = Business::factory()->create();
        AnalyticsEvent::factory()->for($business)->create(['event_type' => AnalyticsEventType::Scan, 'occurred_at' => now()]);
        AnalyticsEvent::factory()->for($business)->create(['event_type' => AnalyticsEventType::MenuView, 'occurred_at' => now()->subDays(3)]);
        AnalyticsEvent::factory()->for($business)->create(['event_type' => AnalyticsEventType::PageView, 'occurred_at' => now()->subDays(15)]);
        Sanctum::actingAs(User::factory()->godfather()->create());

        $this->getJson('/api/admin/v1/analytics/summary')
            ->assertOk()
            ->assertJsonPath('data.today.scan', 1)
            ->assertJsonPath('data.today.menu_view', 0)
            ->assertJsonPath('data.7_days.menu_view', 1)
            ->assertJsonPath('data.30_days.page_view', 1);
    }
}
