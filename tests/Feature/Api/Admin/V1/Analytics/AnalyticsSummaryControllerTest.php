<?php

namespace Tests\Feature\Api\Admin\V1\Analytics;

use App\Core\Analytics\AnalyticsEventType;
use App\Core\Analytics\Models\AnalyticsEvent;
use App\Core\Authorization\FoundationRole;
use App\Core\Authorization\ProvisionFoundationRbac;
use App\Core\Business\Models\Branch;
use App\Core\Business\Models\Business;
use App\Core\Qr\Models\QrCode;
use App\Core\Qr\QrCodeType;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\PermissionRegistrar;
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
        AnalyticsEvent::factory()->for($business)->create(['event_type' => AnalyticsEventType::CategoryView, 'occurred_at' => now()->subDays(3)]);
        AnalyticsEvent::factory()->for($business)->create(['event_type' => AnalyticsEventType::ProductView, 'occurred_at' => now()->subDays(3)]);
        AnalyticsEvent::factory()->for($business)->create(['event_type' => AnalyticsEventType::PageView, 'occurred_at' => now()->subDays(15)]);
        $branch = Branch::factory()->for($business)->create();
        $qrCode = QrCode::factory()->for($business)->for($branch)->create([
            'type' => QrCodeType::Table,
            'label' => 'میز ۱۲',
            'table_key' => 'table-12',
        ]);
        AnalyticsEvent::factory()->for($business)->for($branch)->for($qrCode, 'qrCode')->create([
            'event_type' => AnalyticsEventType::Scan,
            'device_class' => 'mobile',
            'occurred_at' => now(),
        ]);
        Sanctum::actingAs(User::factory()->godfather()->create());

        $this->getJson('/api/admin/v1/analytics/summary')
            ->assertOk()
            ->assertJsonPath('data.today.scan', 2)
            ->assertJsonPath('data.today.menu_view', 0)
            ->assertJsonPath('data.7_days.menu_view', 1)
            ->assertJsonPath('data.7_days.category_view', 1)
            ->assertJsonPath('data.7_days.product_view', 1)
            ->assertJsonPath('data.30_days.page_view', 1)
            ->assertJsonFragment(['device_class' => 'mobile', 'count' => 1])
            ->assertJsonFragment(['table_key' => 'table-12', 'count' => 1]);
    }

    public function test_business_summary_and_breakdowns_exclude_other_business_events(): void
    {
        $business = Business::factory()->create();
        $otherBusiness = Business::factory()->create();
        AnalyticsEvent::factory()->for($business)->create(['event_type' => AnalyticsEventType::MenuView, 'device_class' => 'mobile']);
        AnalyticsEvent::factory()->for($otherBusiness)->count(2)->create(['event_type' => AnalyticsEventType::MenuView, 'device_class' => 'desktop']);

        $user = User::factory()->for($business)->create();
        app(ProvisionFoundationRbac::class)->execute($business);
        app(PermissionRegistrar::class)->setPermissionsTeamId($business->id);
        $user->assignRole(FoundationRole::BusinessOwner->value);
        Sanctum::actingAs($user);

        $this->getJson('/api/admin/v1/analytics/summary')
            ->assertOk()
            ->assertJsonPath('data.30_days.menu_view', 1)
            ->assertJsonCount(1, 'data.breakdowns.devices_30_days')
            ->assertJsonPath('data.breakdowns.devices_30_days.0.device_class', 'mobile')
            ->assertJsonPath('data.breakdowns.devices_30_days.0.count', 1);
    }
}
