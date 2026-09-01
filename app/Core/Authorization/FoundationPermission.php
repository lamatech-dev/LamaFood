<?php

namespace App\Core\Authorization;

enum FoundationPermission: string
{
    case SettingsView = 'settings.view';
    case SettingsManage = 'settings.manage';
    case UsersManage = 'users.manage';
    case AuditView = 'audit.view';
    case SystemView = 'system.view';
    case ModulesView = 'modules.view';
    case ModulesManage = 'modules.manage';
}
