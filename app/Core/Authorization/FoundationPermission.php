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
    case CmsView = 'cms.view';
    case CmsEdit = 'cms.edit';
    case CmsPublish = 'cms.publish';
    case MediaView = 'media.view';
    case MediaManage = 'media.manage';
    case MenuView = 'menu.view';
    case MenuEdit = 'menu.edit';
    case MenuPublish = 'menu.publish';
    case MenuPrice = 'menu.price';
    case MenuAvailability = 'menu.availability';
    case QrView = 'qr.view';
    case QrManage = 'qr.manage';
    case AnalyticsView = 'analytics.view';
    case BackupView = 'backup.view';
    case BackupCreate = 'backup.create';
    case BackupRestore = 'backup.restore';
}
