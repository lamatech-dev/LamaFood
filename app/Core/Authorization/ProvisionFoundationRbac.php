<?php

namespace App\Core\Authorization;

use App\Core\Business\Models\Business;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class ProvisionFoundationRbac
{
    public function execute(Business $business): void
    {
        app(PermissionRegistrar::class)->setPermissionsTeamId($business->id);

        $permissions = collect(FoundationPermission::cases())
            ->mapWithKeys(fn (FoundationPermission $permission): array => [
                $permission->value => Permission::findOrCreate($permission->value, 'web'),
            ]);

        $allPermissions = $permissions->values()->all();

        Role::findOrCreate(FoundationRole::LamatechSuperAdmin->value, 'web')
            ->syncPermissions($allPermissions);
        Role::findOrCreate(FoundationRole::BusinessOwner->value, 'web')
            ->syncPermissions($allPermissions);
        Role::findOrCreate(FoundationRole::ContentEditor->value, 'web')
            ->syncPermissions([
                $permissions[FoundationPermission::SettingsView->value],
                $permissions[FoundationPermission::ModulesView->value],
                $permissions[FoundationPermission::CmsView->value],
                $permissions[FoundationPermission::CmsEdit->value],
                $permissions[FoundationPermission::MediaView->value],
                $permissions[FoundationPermission::MediaManage->value],
                $permissions[FoundationPermission::MenuView->value],
                $permissions[FoundationPermission::MenuEdit->value],
                $permissions[FoundationPermission::MenuPublish->value],
                $permissions[FoundationPermission::QrView->value],
                $permissions[FoundationPermission::AnalyticsView->value],
            ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
