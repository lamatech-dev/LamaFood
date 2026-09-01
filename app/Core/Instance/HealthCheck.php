<?php

namespace App\Core\Instance;

use App\Core\Backup\BackupStatus;
use App\Core\Backup\Models\BackupRecord;
use Illuminate\Support\Facades\DB;
use Throwable;

class HealthCheck
{
    /** @return array{status: string, checks: array{application: string, database: string, backup: string}} */
    public function run(): array
    {
        $database = 'ok';

        try {
            DB::select('SELECT 1');
        } catch (Throwable) {
            $database = 'failed';
        }

        $lastBackup = BackupRecord::query()->where('status', BackupStatus::Completed)->latest('completed_at')->first();
        $backup = match (true) {
            $lastBackup === null => 'missing',
            $lastBackup->completed_at?->isBefore(now()->subHours(26)) === true => 'stale',
            $lastBackup->verified_at === null => 'unverified',
            default => 'ok',
        };

        return [
            'status' => $database === 'ok' && (! app()->isProduction() || $backup === 'ok') ? 'ok' : 'degraded',
            'checks' => [
                'application' => 'ok',
                'database' => $database,
                'backup' => $backup,
            ],
        ];
    }
}
