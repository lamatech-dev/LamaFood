<?php

namespace Tests\Feature\Api\Admin\V1\Auth;

use App\Core\Audit\Models\AuditLog;
use App\Core\Business\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticatedSessionControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_valid_godfather_credentials_create_a_stateful_session_without_a_personal_token(): void
    {
        User::factory()->godfather()->create(['password' => Hash::make('local-secret')]);
        $this->withHeader('Origin', 'http://localhost')->get('/sanctum/csrf-cookie')->assertNoContent();
        $sessionIdBeforeLogin = $this->app['session.store']->getId();

        $response = $this->withHeader('Origin', 'http://localhost')->postJson('/api/admin/v1/login', [
            'identifier' => 'godfather',
            'password' => 'local-secret',
            'device_name' => 'foundation-test',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.user.name', 'Godfather')
            ->assertJsonStructure(['data' => ['user' => ['name', 'username']]])
            ->assertJsonMissingPath('data.token')
            ->assertJsonMissingPath('data.user.is_godfather');
        $this->assertAuthenticatedAs(User::query()->where('username', 'godfather')->sole());
        $this->assertNotSame($sessionIdBeforeLogin, $this->app['session.store']->getId());
        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->assertSame('auth.login', AuditLog::query()->sole()->action);
        $this->assertArrayNotHasKey('ip', AuditLog::query()->sole()->context);

        $this->getJson('/api/admin/v1/me')
            ->assertOk()
            ->assertJsonPath('data.roles', [])
            ->assertJsonFragment(['users.manage']);
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

    public function test_authenticated_session_can_be_used_and_is_invalidated_on_logout(): void
    {
        $user = User::factory()->for(Business::factory())->create([
            'email' => 'owner@example.com',
            'password' => Hash::make('local-secret'),
        ]);

        $this->withHeader('Origin', 'http://localhost')->postJson('/api/admin/v1/login', [
            'identifier' => 'owner@example.com',
            'password' => 'local-secret',
            'device_name' => 'foundation-test',
        ])->assertOk();

        $this->getJson('/api/admin/v1/me')
            ->assertOk()
            ->assertJsonPath('data.email', $user->email);

        $this->postJson('/api/admin/v1/logout')->assertNoContent();
        $this->assertGuest('web');
        $this->app['auth']->forgetGuards();
        $this->getJson('/api/admin/v1/me')->assertUnauthorized();
        $this->assertSame(['auth.login', 'auth.logout'], AuditLog::query()->orderBy('id')->pluck('action')->all());
    }
}
