<?php

namespace Tests\Feature\Api\Admin\V1\Auth;

use App\Core\Audit\Models\AuditLog;
use App\Core\Business\Models\Business;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_sends_a_reset_link_to_a_normal_business_user_without_revealing_account_state(): void
    {
        Notification::fake();
        $business = Business::factory()->create();
        $user = User::factory()->for($business)->create(['email' => 'manager@example.com']);

        $response = $this->postJson('/api/admin/v1/forgot-password', ['email' => 'MANAGER@example.com']);

        $response->assertOk()->assertJsonPath('data.message', 'If the account is eligible, a password reset link has been sent.');
        Notification::assertSentTo($user, ResetPassword::class, fn (ResetPassword $notification): bool => Password::tokenExists($user, $notification->token));
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'auth.password_reset_requested',
            'subject_id' => $user->id,
        ]);
    }

    public function test_unknown_and_godfather_accounts_receive_the_same_generic_response_without_a_token(): void
    {
        Notification::fake();
        $godfather = User::factory()->godfather()->create();

        $unknown = $this->postJson('/api/admin/v1/forgot-password', ['email' => 'missing@example.com']);
        $protected = $this->postJson('/api/admin/v1/forgot-password', ['email' => $godfather->email]);

        $unknown->assertOk();
        $protected->assertOk();
        $this->assertSame($unknown->json('data.message'), $protected->json('data.message'));
        Notification::assertNothingSent();
        $this->assertDatabaseCount('password_reset_tokens', 0);
    }

    public function test_password_reset_requests_are_rate_limited_by_email_and_request_source(): void
    {
        Notification::fake();

        for ($attempt = 0; $attempt < 3; $attempt++) {
            $this->postJson('/api/admin/v1/forgot-password', ['email' => 'rate-limit@example.com'])->assertOk();
        }

        $this->postJson('/api/admin/v1/forgot-password', ['email' => 'rate-limit@example.com'])->assertTooManyRequests();
    }

    public function test_valid_token_resets_password_revokes_tokens_and_records_audit_event(): void
    {
        $business = Business::factory()->create();
        $user = User::factory()->for($business)->create(['email' => 'owner@example.com']);
        $user->createToken('existing-session');
        DB::table('sessions')->insert([
            'id' => 'existing-session',
            'user_id' => $user->id,
            'ip_address' => null,
            'user_agent' => 'test',
            'payload' => 'test-payload',
            'last_activity' => now()->timestamp,
        ]);
        $token = Password::createToken($user);
        Event::fake([PasswordReset::class]);

        $this->postJson('/api/admin/v1/reset-password', [
            'email' => $user->email,
            'token' => $token,
            'password' => 'NewSecure#123',
            'password_confirmation' => 'NewSecure#123',
        ])->assertOk()->assertJsonPath('data.message', 'Your password has been reset. You may now sign in.');

        $user->refresh();
        $this->assertTrue(Hash::check('NewSecure#123', $user->password));
        $this->assertDatabaseMissing('personal_access_tokens', ['tokenable_id' => $user->id]);
        $this->assertDatabaseMissing('sessions', ['user_id' => $user->id]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'auth.password_reset_completed',
            'subject_id' => $user->id,
        ]);
        Event::assertDispatched(PasswordReset::class, fn (PasswordReset $event): bool => $event->user->is($user));
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $user->email]);
    }

    public function test_expired_token_is_rejected_without_changing_the_password(): void
    {
        $business = Business::factory()->create();
        $user = User::factory()->for($business)->create(['email' => 'expired@example.com']);
        $originalPassword = $user->password;
        $token = Password::createToken($user);
        DB::table('password_reset_tokens')->where('email', $user->email)->update(['created_at' => now()->subHours(2)]);

        $this->postJson('/api/admin/v1/reset-password', [
            'email' => $user->email,
            'token' => $token,
            'password' => 'NewSecure#123',
            'password_confirmation' => 'NewSecure#123',
        ])->assertUnprocessable()->assertJsonValidationErrors('email');

        $this->assertSame($originalPassword, $user->fresh()->password);
        $this->assertSame(0, AuditLog::query()->where('action', 'auth.password_reset_completed')->count());
    }

    public function test_weak_password_and_mismatched_confirmation_are_rejected(): void
    {
        $business = Business::factory()->create();
        $user = User::factory()->for($business)->create();

        $this->postJson('/api/admin/v1/reset-password', [
            'email' => $user->email,
            'token' => 'invalid-token',
            'password' => 'weak-password',
            'password_confirmation' => 'different-password',
        ])->assertUnprocessable()->assertJsonValidationErrors('password');
    }

    public function test_godfather_cannot_be_reset_even_with_a_manually_created_broker_token(): void
    {
        $godfather = User::factory()->godfather()->create();
        $originalPassword = $godfather->password;
        $token = Password::createToken($godfather);

        $this->postJson('/api/admin/v1/reset-password', [
            'email' => $godfather->email,
            'token' => $token,
            'password' => 'NewSecure#123',
            'password_confirmation' => 'NewSecure#123',
        ])->assertUnprocessable()->assertJsonValidationErrors('email');

        $this->assertSame($originalPassword, $godfather->fresh()->password);
    }

    public function test_password_reset_pages_are_noindex_and_do_not_expose_godfather_details(): void
    {
        $this->withoutVite();

        $this->get('/admin/forgot-password')
            ->assertOk()
            ->assertSee('noindex,nofollow')
            ->assertDontSee('godfather', false);
        $this->get('/admin/reset-password/example-token?email=user%40example.com')
            ->assertOk()
            ->assertSee('noindex,nofollow')
            ->assertSee('example-token')
            ->assertDontSee('LAMATECH_GODFATHER_PASSWORD');
    }
}
