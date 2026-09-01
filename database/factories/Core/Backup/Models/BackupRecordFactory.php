<?php

namespace Database\Factories\Core\Backup\Models;

use App\Core\Backup\BackupStatus;
use App\Core\Backup\BackupType;
use App\Core\Backup\Models\BackupRecord;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<BackupRecord> */
class BackupRecordFactory extends Factory
{
    protected $model = BackupRecord::class;

    public function definition(): array
    {
        return [
            'public_id' => (string) Str::ulid(),
            'type' => BackupType::Database,
            'status' => BackupStatus::Completed,
            'disk' => 'backups',
            'path' => 'test/database.sql.gz',
            'checksum' => hash('sha256', 'backup'),
            'storage_encrypted' => false,
            'size' => 6,
            'started_at' => now(),
            'completed_at' => now(),
            'expires_at' => now()->addDays(14),
        ];
    }
}
