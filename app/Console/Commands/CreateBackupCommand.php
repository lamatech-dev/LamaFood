<?php

namespace App\Console\Commands;

use App\Core\Backup\Actions\CreateBackup;
use App\Core\Backup\BackupType;
use Illuminate\Console\Command;

class CreateBackupCommand extends Command
{
    protected $signature = 'backup:create {type=database : database, full, or pre_release}';

    protected $description = 'Create a secret-excluding instance backup';

    public function handle(CreateBackup $create): int
    {
        $type = BackupType::tryFrom((string) $this->argument('type'));
        if ($type === null) {
            $this->error('Invalid backup type.');

            return self::INVALID;
        }
        $record = $create->execute($type);
        $this->info("Backup {$record->public_id} completed and requires independent restore verification.");

        return self::SUCCESS;
    }
}
