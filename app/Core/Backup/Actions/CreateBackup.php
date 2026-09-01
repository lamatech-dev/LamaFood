<?php

namespace App\Core\Backup\Actions;

use App\Core\Audit\AuditRecorder;
use App\Core\Backup\BackupStatus;
use App\Core\Backup\BackupType;
use App\Core\Backup\Contracts\DatabaseDumper;
use App\Core\Backup\Models\BackupRecord;
use App\Core\Instance\Models\InstanceMetadata;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Phar;
use PharData;
use RuntimeException;
use Throwable;

class CreateBackup
{
    public function __construct(
        private readonly DatabaseDumper $database,
        private readonly AuditRecorder $audit,
    ) {}

    public function execute(BackupType $type): BackupRecord
    {
        $disk = (string) config('backup.disk');
        $encrypted = config('backup.storage_encrypted') === true;
        $record = BackupRecord::query()->create([
            'public_id' => (string) Str::ulid(),
            'type' => $type,
            'status' => BackupStatus::Running,
            'disk' => $disk,
            'storage_encrypted' => $encrypted,
            'started_at' => now(),
        ]);
        $workingDirectory = storage_path('app/private/backup-work/'.$record->public_id);

        try {
            $this->assertProductionStoragePolicy($encrypted);
            File::ensureDirectoryExists($workingDirectory, 0700);
            $databaseDump = $workingDirectory.'/database.sql';
            $this->database->dump($databaseDump);
            $manifest = $this->manifest($type);
            File::put($workingDirectory.'/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
            $artifact = $type === BackupType::Database
                ? $this->compressDatabase($databaseDump, $workingDirectory)
                : $this->buildFullArchive($workingDirectory, $databaseDump);
            $path = now()->format('Y/m/d').'/'.$record->public_id.'/'.basename($artifact);
            $stream = fopen($artifact, 'rb');
            if ($stream === false || ! Storage::disk($disk)->put($path, $stream)) {
                throw new RuntimeException('The backup artifact could not be written to its configured disk.');
            }
            if (is_resource($stream)) {
                fclose($stream);
            }
            $checksum = hash_file('sha256', $artifact);
            $size = filesize($artifact);
            if ($checksum === false || $size === false) {
                throw new RuntimeException('The backup artifact metadata could not be calculated.');
            }
            $record->update([
                'status' => BackupStatus::Completed,
                'path' => $path,
                'checksum' => $checksum,
                'size' => $size,
                'manifest_json' => $manifest,
                'completed_at' => now(),
                'expires_at' => now()->addDays($type === BackupType::Database ? (int) config('backup.database_retention_days') : (int) config('backup.full_retention_days')),
            ]);
            $this->audit->record('backup.completed', subject: $record, after: $record->fresh()->toArray());

            return $record->fresh();
        } catch (Throwable $exception) {
            $record->update([
                'status' => BackupStatus::Failed,
                'failure_message' => Str::limit($exception->getMessage(), 2000),
                'completed_at' => now(),
            ]);
            $this->audit->record('backup.failed', subject: $record, after: ['type' => $type->value, 'status' => 'failed']);

            throw $exception;
        } finally {
            File::deleteDirectory($workingDirectory);
        }
    }

    private function assertProductionStoragePolicy(bool $encrypted): void
    {
        if (app()->isProduction() && (! $encrypted || config('backup.external_storage') !== true)) {
            throw new RuntimeException('Production backups require encrypted external storage.');
        }
    }

    /** @return array<string, mixed> */
    private function manifest(BackupType $type): array
    {
        $instance = InstanceMetadata::query()->first();

        return [
            'format_version' => 1,
            'type' => $type->value,
            'created_at' => now()->toAtomString(),
            'instance_id' => $instance?->instance_id,
            'core_version' => $instance?->core_version,
            'secret_policy' => 'excluded_reprovision_from_external_escrow',
            'required_secret_references' => config('backup.required_secret_references', []),
            'excluded_paths' => config('backup.excluded_paths', []),
            'contents' => $type === BackupType::Database ? ['database.sql'] : ['database.sql', 'uploads/'],
        ];
    }

    private function compressDatabase(string $databaseDump, string $workingDirectory): string
    {
        $destination = $workingDirectory.'/database.sql.gz';
        $input = fopen($databaseDump, 'rb');
        $output = gzopen($destination, 'wb9');
        if ($input === false || $output === false) {
            throw new RuntimeException('The database backup could not be compressed.');
        }
        while (! feof($input)) {
            $chunk = fread($input, 1024 * 1024);
            if ($chunk === false || gzwrite($output, $chunk) === false) {
                throw new RuntimeException('The database backup compression failed.');
            }
        }
        fclose($input);
        gzclose($output);

        return $destination;
    }

    private function buildFullArchive(string $workingDirectory, string $databaseDump): string
    {
        $tarPath = $workingDirectory.'/full-backup.tar';
        $archive = new PharData($tarPath);
        $archive->addFile($databaseDump, 'database.sql');
        $archive->addFile($workingDirectory.'/manifest.json', 'manifest.json');
        $mediaRoot = Storage::disk('public')->path('');
        if (File::isDirectory($mediaRoot)) {
            foreach (File::allFiles($mediaRoot) as $file) {
                if (! $file->isLink()) {
                    $archive->addFile($file->getPathname(), 'uploads/'.$file->getRelativePathname());
                }
            }
        }
        $archive->compress(Phar::GZ);
        unset($archive);
        File::delete($tarPath);

        return $tarPath.'.gz';
    }
}
