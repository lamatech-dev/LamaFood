<?php

namespace App\Console\Commands;

use App\Core\Backup\Actions\RestoreBackup;
use App\Core\Backup\Models\BackupRecord;
use App\Core\Instance\Models\InstanceMetadata;
use Illuminate\Console\Command;
use Throwable;

class RestoreBackupCommand extends Command
{
    protected $signature = 'backup:restore
        {public_id : Backup record public ID}
        {--safety-backup= : Newer verified pre_release backup public ID}
        {--execute : Perform the destructive restore after preflight}
        {--confirmation= : Exact non-secret confirmation phrase}';

    protected $description = 'Preflight or execute a guarded instance backup restore';

    public function handle(RestoreBackup $restore): int
    {
        $target = BackupRecord::query()->where('public_id', $this->argument('public_id'))->first();
        $safetyBackup = BackupRecord::query()->where('public_id', $this->option('safety-backup'))->first();
        if ($target === null || $safetyBackup === null) {
            $this->error('Both target and safety backup records must exist.');

            return self::INVALID;
        }

        try {
            $plan = $restore->preflight($target, $safetyBackup);
        } catch (Throwable $exception) {
            $this->error('Restore preflight failed: '.$exception->getMessage());

            return self::FAILURE;
        }

        $phrase = $this->confirmationPhrase($target);
        $this->table(['Check', 'Value'], [
            ['Target', $plan['target']],
            ['Safety backup', $plan['safety_backup']],
            ['Type', $plan['type']],
            ['Database bytes', (string) $plan['database_bytes']],
            ['Upload files', (string) $plan['upload_files']],
        ]);

        if (! $this->option('execute')) {
            $this->info('Preflight passed. No application data was changed.');
            $this->line('Execution requires maintenance mode and: --execute --confirmation="'.$phrase.'"');

            return self::SUCCESS;
        }

        if (! app()->isDownForMaintenance()) {
            $this->error('Execution requires application maintenance mode. Run php artisan down first.');

            return self::FAILURE;
        }

        if (! hash_equals($phrase, (string) $this->option('confirmation'))) {
            $this->error('The restore confirmation phrase is invalid.');

            return self::INVALID;
        }

        try {
            $restore->execute($target, $safetyBackup);
        } catch (Throwable) {
            $this->error('Restore failed. Keep maintenance mode active and use the safety backup/runbook.');

            return self::FAILURE;
        }

        $this->info('Restore completed. Keep maintenance mode active until validation is complete; run php artisan up only after checks pass.');

        return self::SUCCESS;
    }

    private function confirmationPhrase(BackupRecord $target): string
    {
        $instanceId = (string) InstanceMetadata::query()->value('instance_id');

        return "RESTORE {$target->public_id} ON {$instanceId}";
    }
}
