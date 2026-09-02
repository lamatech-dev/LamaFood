<?php

namespace Tests\Feature\Api\Admin\V1\System;

use App\Core\Backup\Models\BackupRecord;
use App\Core\Instance\Models\InstanceMetadata;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class HealthControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_returns_401_when_unauthenticated(): void
    {
        $this->getJson('/api/admin/v1/system/health')->assertUnauthorized();
    }

    public function test_godfather_bypass_can_read_healthy_foundation_status(): void
    {
        Storage::fake('health-test');
        config(['health.storage_disk' => 'health-test', 'queue.default' => 'sync']);
        Sanctum::actingAs(User::factory()->godfather()->create());

        $this->getJson('/api/admin/v1/system/health')
            ->assertOk()
            ->assertJsonPath('data.status', 'ok')
            ->assertJsonPath('data.checks.application', 'ok')
            ->assertJsonPath('data.checks.database', 'ok')
            ->assertJsonPath('data.checks.storage', 'ok')
            ->assertJsonPath('data.checks.queue', 'ok')
            ->assertJsonPath('data.checks.scheduler', 'missing')
            ->assertJsonPath('data.checks.backup_freshness', 'missing');
    }

    public function test_scheduler_command_records_a_fresh_heartbeat(): void
    {
        $this->artisan('health:scheduler-heartbeat')->assertSuccessful();

        $this->assertTrue(InstanceMetadata::query()->sole()->last_health_check_at?->isAfter(now()->subMinute()));
    }

    public function test_production_health_is_ok_when_all_application_checks_are_fresh(): void
    {
        Storage::fake('health-test');
        config(['health.storage_disk' => 'health-test', 'queue.default' => 'sync']);
        $this->app->detectEnvironment(fn (): string => 'production');
        InstanceMetadata::factory()->create(['last_health_check_at' => now()]);
        BackupRecord::factory()->create(['completed_at' => now(), 'verified_at' => now()]);
        Sanctum::actingAs(User::factory()->godfather()->create());

        $this->getJson('/api/admin/v1/system/health')
            ->assertOk()
            ->assertJsonPath('data.status', 'ok')
            ->assertJsonPath('data.checks.scheduler', 'ok')
            ->assertJsonPath('data.checks.backup_freshness', 'ok');
    }

    public function test_production_health_is_degraded_for_stale_scheduler_and_backup(): void
    {
        Storage::fake('health-test');
        config(['health.storage_disk' => 'health-test', 'queue.default' => 'sync']);
        $this->app->detectEnvironment(fn (): string => 'production');
        InstanceMetadata::factory()->create(['last_health_check_at' => now()->subMinutes(10)]);
        BackupRecord::factory()->create(['completed_at' => now()->subHours(30), 'verified_at' => now()->subHours(30)]);
        Sanctum::actingAs(User::factory()->godfather()->create());

        $this->getJson('/api/admin/v1/system/health')
            ->assertServiceUnavailable()
            ->assertJsonPath('data.status', 'degraded')
            ->assertJsonPath('data.checks.scheduler', 'stale')
            ->assertJsonPath('data.checks.backup_freshness', 'stale');
    }

    public function test_storage_failure_degrades_health_without_exposing_exception_details(): void
    {
        config(['health.storage_disk' => 'not-configured', 'queue.default' => 'sync']);
        Sanctum::actingAs(User::factory()->godfather()->create());

        $this->getJson('/api/admin/v1/system/health')
            ->assertServiceUnavailable()
            ->assertJsonPath('data.checks.storage', 'failed')
            ->assertJsonMissing(['exception']);
    }
}
