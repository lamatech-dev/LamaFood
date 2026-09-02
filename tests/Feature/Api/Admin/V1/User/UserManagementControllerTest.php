<?php

namespace Tests\Feature\Api\Admin\V1\User;

use App\Core\Authorization\FoundationRole;
use App\Core\Authorization\ProvisionFoundationRbac;
use App\Core\Business\Models\Business;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class UserManagementControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_authorized_owner_can_create_business_user_with_allowed_role_and_safe_password_setup(): void
    {
        Notification::fake();
        $business = Business::factory()->create();
        $owner = $this->userWithRole($business, FoundationRole::BusinessOwner);
        Sanctum::actingAs($owner);

        $response = $this->postJson('/api/admin/v1/users', [
            'name' => 'Denardi Editor',
            'username' => 'denardi-editor',
            'email' => 'EDITOR@EXAMPLE.COM',
            'role' => FoundationRole::ContentEditor->value,
            'is_active' => true,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.user.email', 'editor@example.com')
            ->assertJsonPath('data.user.role', FoundationRole::ContentEditor->value)
            ->assertJsonPath('data.user.is_active', true)
            ->assertJsonMissingPath('data.user.password')
            ->assertJsonMissingPath('data.user.is_godfather');
        $created = User::query()->where('email', 'editor@example.com')->sole();
        $this->assertSame($business->id, $created->business_id);
        $this->assertTrue($created->hasRole(FoundationRole::ContentEditor->value));
        Notification::assertSentTo($created, ResetPassword::class);
        $this->assertDatabaseHas('audit_logs', ['action' => 'user.created', 'subject_id' => $created->id]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'user.password_setup_requested', 'subject_id' => $created->id]);
    }

    public function test_content_editor_cannot_create_or_list_users(): void
    {
        $business = Business::factory()->create();
        $editor = $this->userWithRole($business, FoundationRole::ContentEditor);
        Sanctum::actingAs($editor);

        $this->getJson('/api/admin/v1/users')->assertForbidden();
        $this->postJson('/api/admin/v1/users', [
            'name' => 'Blocked User',
            'email' => 'blocked@example.com',
            'role' => FoundationRole::ContentEditor->value,
        ])->assertForbidden();
        $this->assertDatabaseCount('users', 1);
    }

    public function test_inactive_user_can_be_created_without_sending_a_password_setup_link(): void
    {
        Notification::fake();
        $business = Business::factory()->create();
        $owner = $this->userWithRole($business, FoundationRole::BusinessOwner);
        Sanctum::actingAs($owner);

        $this->postJson('/api/admin/v1/users', [
            'name' => 'Inactive Editor',
            'email' => 'inactive-editor@example.com',
            'role' => FoundationRole::ContentEditor->value,
            'is_active' => false,
        ])->assertCreated()->assertJsonPath('data.password_setup_sent', false);

        $created = User::query()->where('email', 'inactive-editor@example.com')->sole();
        Notification::assertNothingSent();
        $this->assertDatabaseMissing('audit_logs', ['action' => 'user.password_setup_requested', 'subject_id' => $created->id]);
    }

    public function test_godfather_can_manage_users_in_the_configured_business_without_becoming_visible(): void
    {
        Notification::fake();
        $business = Business::factory()->create(['slug' => 'denardi']);
        config()->set('denardi.business_slug', 'denardi');
        $this->userWithRole($business, FoundationRole::BusinessOwner);
        $godfather = User::factory()->godfather()->create();
        Sanctum::actingAs($godfather);

        $response = $this->postJson('/api/admin/v1/users', [
            'name' => 'Managed by Lamatech',
            'email' => 'managed@example.com',
            'role' => FoundationRole::ContentEditor->value,
            'is_active' => true,
        ])->assertCreated()->assertJsonPath('data.user.role', FoundationRole::ContentEditor->value);

        $this->assertSame($business->id, User::query()->findOrFail($response->json('data.user.id'))->business_id);
        $this->getJson('/api/admin/v1/users')->assertOk()->assertJsonMissing(['email' => $godfather->email]);
    }

    public function test_listing_is_business_scoped_and_never_exposes_godfather_or_lamatech_role(): void
    {
        $business = Business::factory()->create();
        $owner = $this->userWithRole($business, FoundationRole::BusinessOwner);
        $visible = $this->userWithRole($business, FoundationRole::ContentEditor);
        $protectedLamatechUser = $this->userWithRole($business, FoundationRole::LamatechSuperAdmin);
        $otherBusiness = Business::factory()->create();
        $other = $this->userWithRole($otherBusiness, FoundationRole::BusinessOwner);
        $godfather = User::factory()->godfather()->create();
        app(PermissionRegistrar::class)->setPermissionsTeamId($business->id);
        Sanctum::actingAs($owner);

        $response = $this->getJson('/api/admin/v1/users')->assertOk();

        $response->assertJsonFragment(['email' => $visible->email])
            ->assertJsonMissing(['email' => $other->email])
            ->assertJsonMissing(['email' => $godfather->email])
            ->assertJsonMissing(['email' => $protectedLamatechUser->email])
            ->assertJsonMissing(['value' => FoundationRole::LamatechSuperAdmin->value]);
        $this->assertCount(2, $response->json('data.users'));
    }

    public function test_role_escalation_self_access_changes_and_last_owner_lockout_are_rejected(): void
    {
        $business = Business::factory()->create();
        $owner = $this->userWithRole($business, FoundationRole::BusinessOwner);
        Sanctum::actingAs($owner);

        $this->postJson('/api/admin/v1/users', [
            'name' => 'Escalated',
            'email' => 'escalated@example.com',
            'role' => FoundationRole::LamatechSuperAdmin->value,
        ])->assertUnprocessable()->assertJsonValidationErrors('role');

        $this->putJson("/api/admin/v1/users/{$owner->id}", [
            'name' => $owner->name,
            'username' => $owner->username,
            'email' => $owner->email,
            'role' => FoundationRole::ContentEditor->value,
            'is_active' => true,
        ])->assertUnprocessable()->assertJsonValidationErrors('role');

        $this->deleteJson("/api/admin/v1/users/{$owner->id}")
            ->assertUnprocessable()
            ->assertJsonValidationErrors('role');
        $this->assertTrue($owner->fresh()->is_active);
    }

    public function test_owner_can_update_role_and_status_while_revoking_target_sessions_and_tokens(): void
    {
        $business = Business::factory()->create();
        $owner = $this->userWithRole($business, FoundationRole::BusinessOwner);
        $target = $this->userWithRole($business, FoundationRole::ContentEditor);
        $target->createToken('legacy-test-token');
        $this->insertSessionFor($target);
        Sanctum::actingAs($owner);

        $this->putJson("/api/admin/v1/users/{$target->id}", [
            'name' => 'Updated Editor',
            'username' => 'updated-editor',
            'email' => 'updated@example.com',
            'role' => FoundationRole::BusinessOwner->value,
            'is_active' => false,
        ])->assertOk()
            ->assertJsonPath('data.name', 'Updated Editor')
            ->assertJsonPath('data.role', FoundationRole::BusinessOwner->value)
            ->assertJsonPath('data.is_active', false);

        $target = $target->fresh();
        $this->assertFalse($target->is_active);
        $this->assertTrue($target->hasRole(FoundationRole::BusinessOwner->value));
        $this->assertDatabaseMissing('sessions', ['user_id' => $target->id]);
        $this->assertDatabaseMissing('personal_access_tokens', ['tokenable_id' => $target->id]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'user.updated', 'subject_id' => $target->id]);
    }

    public function test_delete_safely_deactivates_instead_of_removing_user(): void
    {
        $business = Business::factory()->create();
        $owner = $this->userWithRole($business, FoundationRole::BusinessOwner);
        $target = $this->userWithRole($business, FoundationRole::ContentEditor);
        Sanctum::actingAs($owner);

        $this->deleteJson("/api/admin/v1/users/{$target->id}")
            ->assertOk()
            ->assertJsonPath('data.is_active', false);

        $this->assertModelExists($target);
        $this->assertFalse($target->fresh()->is_active);
        $this->assertDatabaseHas('audit_logs', ['action' => 'user.deactivated', 'subject_id' => $target->id]);
    }

    public function test_password_reset_initiation_is_scoped_audited_and_unavailable_for_inactive_or_godfather_accounts(): void
    {
        Notification::fake();
        $business = Business::factory()->create();
        $owner = $this->userWithRole($business, FoundationRole::BusinessOwner);
        $target = $this->userWithRole($business, FoundationRole::ContentEditor);
        $inactive = $this->userWithRole($business, FoundationRole::ContentEditor, false);
        $otherBusiness = Business::factory()->create();
        $other = $this->userWithRole($otherBusiness, FoundationRole::ContentEditor);
        $godfather = User::factory()->godfather()->create();
        app(PermissionRegistrar::class)->setPermissionsTeamId($business->id);
        Sanctum::actingAs($owner);

        $this->postJson("/api/admin/v1/users/{$target->id}/password-reset")
            ->assertOk()
            ->assertJsonPath('data.sent', true);
        Notification::assertSentTo($target, ResetPassword::class);
        $this->assertDatabaseHas('audit_logs', ['action' => 'user.password_reset_initiated', 'subject_id' => $target->id]);

        $this->postJson("/api/admin/v1/users/{$inactive->id}/password-reset")->assertUnprocessable();
        $this->postJson("/api/admin/v1/users/{$other->id}/password-reset")->assertNotFound();
        $this->postJson("/api/admin/v1/users/{$godfather->id}/password-reset")->assertNotFound();
    }

    public function test_inactive_user_cannot_log_in_or_keep_using_an_authenticated_api_session(): void
    {
        $inactive = User::factory()->create([
            'password' => Hash::make('local-secret'),
            'is_active' => false,
        ]);

        $this->withHeader('Origin', 'http://localhost')->postJson('/api/admin/v1/login', [
            'identifier' => $inactive->email,
            'password' => 'local-secret',
            'device_name' => 'inactive-test',
        ])->assertUnprocessable()->assertJsonValidationErrors('identifier');

        Sanctum::actingAs($inactive);
        $this->getJson('/api/admin/v1/me')->assertForbidden();
    }

    private function userWithRole(Business $business, FoundationRole $role, bool $isActive = true): User
    {
        app(ProvisionFoundationRbac::class)->execute($business);
        app(PermissionRegistrar::class)->setPermissionsTeamId($business->id);
        $user = User::factory()->for($business)->create(['is_active' => $isActive]);
        $user->assignRole($role->value);

        return $user;
    }

    private function insertSessionFor(User $user): void
    {
        DB::table('sessions')->insert([
            'id' => 'session-'.$user->id,
            'user_id' => $user->id,
            'ip_address' => null,
            'user_agent' => 'test',
            'payload' => 'test-payload',
            'last_activity' => now()->timestamp,
        ]);
    }
}
