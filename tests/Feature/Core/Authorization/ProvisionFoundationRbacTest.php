<?php

namespace Tests\Feature\Core\Authorization;

use App\Core\Authorization\FoundationRole;
use App\Core\Authorization\ProvisionFoundationRbac;
use App\Core\Business\Models\Business;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ProvisionFoundationRbacTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_provisions_business_scoped_foundation_roles_and_permissions(): void
    {
        $business = Business::factory()->create();

        app(ProvisionFoundationRbac::class)->execute($business);

        app(PermissionRegistrar::class)->setPermissionsTeamId($business->id);
        $owner = Role::findByName(FoundationRole::BusinessOwner->value);
        $editor = Role::findByName(FoundationRole::ContentEditor->value);

        $this->assertTrue($owner->hasPermissionTo('users.manage'));
        $this->assertTrue($owner->hasPermissionTo('system.view'));
        $this->assertTrue($editor->hasPermissionTo('settings.view'));
        $this->assertFalse($editor->hasPermissionTo('users.manage'));
    }
}
