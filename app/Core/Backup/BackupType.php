<?php

namespace App\Core\Backup;

enum BackupType: string
{
    case Database = 'database';
    case Full = 'full';
    case PreRelease = 'pre_release';
}
