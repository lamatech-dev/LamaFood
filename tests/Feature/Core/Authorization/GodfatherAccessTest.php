<?php

namespace Tests\Feature\Core\Authorization;

use App\Core\Business\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class GodfatherAccessTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_bootstrap_command_creates_invisible_instance_level_account_from_configuration(): void
    {
        config()->set('lamatech.godfather.username', 'godfather');
        config()->set('lamatech.godfather.password', 'rotatable-local-secret');
        config()->set('lamatech.godfather.email', 'godfather@instance.invalid');

        $exitCode = Artisan::call('lamatech:bootstrap-godfather');

        $godfather = User::query()->where('is_godfather', true)->sole();
        $this->assertSame(0, $exitCode);
        $this->assertSame('Godfather', $godfather->name);
        $this->assertNull($godfather->business_id);
        $this->assertTrue(Hash::check('rotatable-local-secret', $godfather->password));
        $this->assertSame(0, User::query()->businessVisible()->count());
        $this->assertDatabaseHas('audit_logs', ['action' => 'lamatech.godfather.bootstrapped']);
    }

    public function test_godfather_bypasses_business_permission_checks_without_a_visible_role(): void
    {
        $godfather = User::factory()->godfather()->create();

        $this->assertTrue(Gate::forUser($godfather)->allows('any.foundation.permission'));
        $this->assertSame([], $godfather->getRoleNames()->all());
    }

    public function test_bootstrap_command_rotates_password_without_source_changes(): void
    {
        config()->set('lamatech.godfather.username', 'godfather');
        config()->set('lamatech.godfather.email', 'godfather@instance.invalid');
        config()->set('lamatech.godfather.password', 'first-local-secret');
        Artisan::call('lamatech:bootstrap-godfather');

        config()->set('lamatech.godfather.password', 'second-local-secret');
        Artisan::call('lamatech:bootstrap-godfather');

        $godfather = User::query()->where('is_godfather', true)->sole();
        $this->assertTrue(Hash::check('second-local-secret', $godfather->password));
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseHas('audit_logs', ['action' => 'lamatech.godfather.credentials_rotated']);
    }

    public function test_business_user_cannot_view_update_or_delete_godfather(): void
    {
        $businessUser = User::factory()->for(Business::factory())->create();
        $godfather = User::factory()->godfather()->create();

        $this->assertFalse(Gate::forUser($businessUser)->allows('view', $godfather));
        $this->assertFalse(Gate::forUser($businessUser)->allows('update', $godfather));
        $this->assertFalse(Gate::forUser($businessUser)->allows('delete', $godfather));
        $this->assertSame(0, User::query()->businessVisible()->where('username', 'godfather')->count());
    }

    public function test_business_relationship_and_visibility_scope_exclude_godfather(): void
    {
        $business = Business::factory()->create();
        User::factory()->for($business)->create();
        User::factory()->godfather()->create();

        $this->assertSame(1, $business->users()->count());
        $this->assertSame(1, User::query()->businessVisible()->count());
    }
}
