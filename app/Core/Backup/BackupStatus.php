<?php

namespace App\Core\Backup;

enum BackupStatus: string
{
    case Running = 'running';
    case Completed = 'completed';
    case Failed = 'failed';
}
