<?php

namespace Tests\Feature\Core\Qr;

use App\Core\Analytics\Models\AnalyticsEvent;
use App\Core\Business\Models\Branch;
use App\Core\Business\Models\Business;
use App\Core\Qr\Actions\CreateQrCode;
use App\Core\Qr\QrCodeType;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class QrRedirectAnalyticsTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_table_qr_redirects_to_localized_branch_menu_and_deduplicates_scans_for_30_minutes(): void
    {
        $business = Business::factory()->create(['slug' => 'denardi']);
        $branch = Branch::factory()->for($business)->create(['slug' => 'central', 'is_default' => true]);
        $actor = User::factory()->for($business)->create();
        $qrCode = app(CreateQrCode::class)->execute($branch, $actor, QrCodeType::Table, 'Table 12', 'table-12');
        $visitor = str_repeat('a', 40);

        $this->withCookie('denardi_visitor', $visitor)
            ->withHeader('Accept-Language', 'ar')
            ->get('/q/'.$qrCode->public_id)
            ->assertRedirect(route('public.menu', ['locale' => 'ar', 'branch' => 'central', 'table' => 'table-12']));
        $this->withCookie('denardi_visitor', $visitor)
            ->withHeader('Accept-Language', 'ar')
            ->get('/q/'.$qrCode->public_id)
            ->assertRedirect();

        $this->assertDatabaseCount('analytics_events', 1);
        $event = AnalyticsEvent::query()->sole();
        $this->assertSame('scan', $event->event_type->value);
        $this->assertSame('ar', $event->locale);
        $this->assertSame(64, mb_strlen($event->visitor_hash));
    }

    public function test_bot_scan_redirects_without_recording_analytics(): void
    {
        $business = Business::factory()->create(['slug' => 'denardi']);
        $branch = Branch::factory()->for($business)->create(['is_default' => true]);
        $actor = User::factory()->for($business)->create();
        $qrCode = app(CreateQrCode::class)->execute($branch, $actor, QrCodeType::Menu, 'Front desk', null);

        $this->withHeader('User-Agent', 'ExampleBot/1.0')->get('/q/'.$qrCode->public_id)->assertRedirect();

        $this->assertDatabaseCount('analytics_events', 0);
    }

    public function test_campaign_type_is_not_a_v1_qr_type_and_table_key_rules_are_explicit(): void
    {
        $this->assertNull(QrCodeType::tryFrom('campaign'));
        $business = Business::factory()->create();
        $branch = Branch::factory()->for($business)->create();
        $actor = User::factory()->for($business)->create();

        $this->expectException(ValidationException::class);
        app(CreateQrCode::class)->execute($branch, $actor, QrCodeType::Table, 'Missing table', null);
    }
}
