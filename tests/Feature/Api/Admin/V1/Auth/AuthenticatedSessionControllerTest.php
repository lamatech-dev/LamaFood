<?php

namespace Tests\Feature\Api\Admin\V1\Auth;

use App\Core\Audit\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticatedSessionControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_valid_godfather_credentials_return_token_and_write_audit_log(): void
    {
        User::factory()->godfather()->create(['password' => Hash::make('local-secret')]);

        $response = $this->postJson('/api/admin/v1/login', [
            'identifier' => 'godfather',
            'password' => 'local-secret',
            'device_name' => 'foundation-test',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.user.name', 'Godfather')
            ->assertJsonStructure(['data' => ['token', 'user' => ['name', 'username']]])
            ->assertJsonMissingPath('data.user.is_godfather');
        $this->assertDatabaseHas('personal_access_tokens', ['name' => 'foundation-test']);
        $this->assertSame('auth.login', AuditLog::query()->sole()->action);
    }

    public function test_returns_422_for_invalid_credentials_without_creating_token(): void
    {
        User::factory()->create(['email' => 'owner@example.com']);

        $this->postJson('/api/admin/v1/login', [
            'identifier' => 'owner@example.com',
            'password' => 'wrong-password',
            'device_name' => 'foundation-test',
        ])->assertUnprocessable()->assertJsonValidationErrors('identifier');

        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->assertDatabaseCount('audit_logs', 0);
    }
}
