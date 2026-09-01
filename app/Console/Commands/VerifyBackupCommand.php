<?php

namespace App\Console\Commands;

use App\Core\Backup\Actions\VerifyBackup;
use App\Core\Backup\Models\BackupRecord;
use Illuminate\Console\Command;

class VerifyBackupCommand extends Command
{
    protected $signature = 'backup:verify {public_id}';

    protected $description = 'Verify a backup artifact checksum';

    public function handle(VerifyBackup $verify): int
    {
        $record = BackupRecord::query()->where('public_id', $this->argument('public_id'))->firstOrFail();
        $verify->execute($record);
        $this->info("Backup {$record->public_id} checksum verified.");

        return self::SUCCESS;
    }
}
