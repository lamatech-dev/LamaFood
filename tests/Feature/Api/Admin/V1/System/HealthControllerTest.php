<?php

namespace Tests\Feature\Api\Admin\V1\System;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
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
        Sanctum::actingAs(User::factory()->godfather()->create());

        $this->getJson('/api/admin/v1/system/health')
            ->assertOk()
            ->assertJsonPath('data.status', 'ok')
            ->assertJsonPath('data.checks.database', 'ok');
    }
}
