<?php

namespace App\Core\Backup\Actions;

use App\Core\Audit\AuditRecorder;
use App\Core\Backup\BackupStatus;
use App\Core\Backup\Models\BackupRecord;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class VerifyBackup
{
    public function __construct(private readonly AuditRecorder $audit) {}

    public function execute(BackupRecord $record): BackupRecord
    {
        if ($record->status !== BackupStatus::Completed || blank($record->path) || blank($record->checksum)) {
            throw new RuntimeException('Only completed backups can be verified.');
        }
        $stream = Storage::disk($record->disk)->readStream((string) $record->path);
        if ($stream === null) {
            throw new RuntimeException('The backup artifact is unavailable.');
        }
        $context = hash_init('sha256');
        hash_update_stream($context, $stream);
        fclose($stream);
        if (! hash_equals((string) $record->checksum, hash_final($context))) {
            throw new RuntimeException('The backup checksum does not match.');
        }
        $record->update(['verified_at' => now()]);
        $this->audit->record('backup.verified', subject: $record, after: ['verified_at' => $record->verified_at?->toAtomString()]);

        return $record->fresh();
    }
}
