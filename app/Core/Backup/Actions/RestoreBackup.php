<?php

namespace App\Core\Backup\Actions;

use App\Core\Audit\AuditRecorder;
use App\Core\Backup\BackupStatus;
use App\Core\Backup\BackupType;
use App\Core\Backup\Contracts\DatabaseRestorer;
use App\Core\Backup\Models\BackupRecord;
use App\Core\Instance\Models\InstanceMetadata;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PharData;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;
use Throwable;

class RestoreBackup
{
    public function __construct(
        private readonly DatabaseRestorer $database,
        private readonly AuditRecorder $audit,
    ) {}

    /**
     * @return array{target: string, safety_backup: string, type: string, instance_id: string, database_bytes: int, upload_files: int}
     */
    public function preflight(BackupRecord $target, BackupRecord $safetyBackup): array
    {
        $workingDirectory = $this->workingDirectory();

        try {
            $this->assertRestorePolicy($target, $safetyBackup);
            $this->assertArtifactChecksum($safetyBackup);
            $prepared = $this->prepareArtifact($target, $workingDirectory);

            return [
                'target' => (string) $target->public_id,
                'safety_backup' => (string) $safetyBackup->public_id,
                'type' => $target->type->value,
                'instance_id' => $this->instanceId(),
                'database_bytes' => (int) File::size($prepared['database']),
                'upload_files' => $prepared['upload_files'],
            ];
        } finally {
            File::deleteDirectory($workingDirectory);
        }
    }

    public function execute(BackupRecord $target, BackupRecord $safetyBackup): void
    {
        if (! app()->isDownForMaintenance()) {
            throw new RuntimeException('Restore execution requires application maintenance mode.');
        }

        $lock = $this->acquireLock();
        $workingDirectory = $this->workingDirectory();
        $targetSnapshot = $this->snapshot($target);
        $safetySnapshot = $this->snapshot($safetyBackup);

        try {
            $this->assertRestorePolicy($target, $safetyBackup);
            $this->assertArtifactChecksum($safetyBackup);
            $prepared = $this->prepareArtifact($target, $workingDirectory);
            $this->audit->record('backup.restore.started', subject: $target, after: [
                'safety_backup_public_id' => $safetyBackup->public_id,
            ]);
            Log::notice('Backup restore started.', $this->logContext($target, $safetyBackup));

            $this->database->restore($prepared['database']);

            if ($prepared['uploads'] !== null) {
                $this->replaceUploads($prepared['uploads'], $workingDirectory);
            }

            $restoredTarget = BackupRecord::query()->updateOrCreate(
                ['public_id' => $targetSnapshot['public_id']],
                $targetSnapshot,
            );
            BackupRecord::query()->updateOrCreate(
                ['public_id' => $safetySnapshot['public_id']],
                $safetySnapshot,
            );
            $this->audit->record('backup.restore.completed', subject: $restoredTarget, after: [
                'safety_backup_public_id' => $safetyBackup->public_id,
                'upload_files' => $prepared['upload_files'],
            ]);
            Log::notice('Backup restore completed.', $this->logContext($target, $safetyBackup));
        } catch (Throwable $exception) {
            Log::error('Backup restore failed; maintenance mode must remain active.', $this->logContext($target, $safetyBackup));

            try {
                $this->audit->record('backup.restore.failed', subject: $target, after: [
                    'safety_backup_public_id' => $safetyBackup->public_id,
                    'failure_class' => $exception::class,
                ]);
            } catch (Throwable) {
                // The restored database may not be queryable; the protected file log remains authoritative.
            }

            throw $exception;
        } finally {
            File::deleteDirectory($workingDirectory);
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    private function assertRestorePolicy(BackupRecord $target, BackupRecord $safetyBackup): void
    {
        foreach ([$target, $safetyBackup] as $record) {
            if ($record->status !== BackupStatus::Completed || blank($record->path) || blank($record->checksum) || $record->verified_at === null) {
                throw new RuntimeException('Restore requires completed, checksum-verified backup records.');
            }
        }

        if ($target->is($safetyBackup)) {
            throw new RuntimeException('The restore target and safety backup must be different records.');
        }

        if ($safetyBackup->type !== BackupType::PreRelease || $safetyBackup->completed_at?->lessThanOrEqualTo($target->completed_at) !== false) {
            throw new RuntimeException('A newer, verified pre_release safety backup is required.');
        }

        $manifest = $this->validatedManifest($target);
        $safetyManifest = $this->validatedManifest($safetyBackup);
        $instance = InstanceMetadata::query()->first();
        if ($instance === null
            || $manifest['instance_id'] !== $instance->instance_id
            || $manifest['core_version'] !== $instance->core_version
            || $safetyManifest['instance_id'] !== $instance->instance_id
            || $safetyManifest['core_version'] !== $instance->core_version) {
            throw new RuntimeException('The backup instance or core version does not match this installation.');
        }

        if (app()->isProduction()) {
            if (config('backup.production_restore_enabled') !== true) {
                throw new RuntimeException('Production restore is disabled by configuration.');
            }

            if (config('backup.external_storage') !== true || ! $target->storage_encrypted || ! $safetyBackup->storage_encrypted) {
                throw new RuntimeException('Production restore requires encrypted external target and safety backups.');
            }
        }
    }

    /** @return array<string, mixed> */
    private function validatedManifest(BackupRecord $record): array
    {
        $manifest = $record->manifest_json;
        if (! is_array($manifest)
            || ($manifest['format_version'] ?? null) !== 1
            || ($manifest['type'] ?? null) !== $record->type->value
            || ($manifest['secret_policy'] ?? null) !== 'excluded_reprovision_from_external_escrow'
            || ! is_string($manifest['instance_id'] ?? null)
            || ! is_string($manifest['core_version'] ?? null)
            || ($manifest['contents'] ?? null) !== ($record->type === BackupType::Database ? ['database.sql'] : ['database.sql', 'uploads/'])) {
            throw new RuntimeException('The backup manifest is missing or incompatible.');
        }

        return $manifest;
    }

    private function assertArtifactChecksum(BackupRecord $record): void
    {
        $stream = Storage::disk($record->disk)->readStream((string) $record->path);
        if ($stream === null) {
            throw new RuntimeException('A required backup artifact is unavailable.');
        }

        $context = hash_init('sha256');
        try {
            hash_update_stream($context, $stream);
        } finally {
            fclose($stream);
        }

        if (! hash_equals((string) $record->checksum, hash_final($context))) {
            throw new RuntimeException('A required backup checksum does not match.');
        }
    }

    /** @return array{database: string, uploads: string|null, upload_files: int} */
    private function prepareArtifact(BackupRecord $target, string $workingDirectory): array
    {
        File::ensureDirectoryExists($workingDirectory, 0700);
        $artifact = $workingDirectory.($target->type === BackupType::Database ? '/artifact.sql.gz' : '/artifact.tar.gz');
        $source = Storage::disk($target->disk)->readStream((string) $target->path);
        $destination = fopen($artifact, 'wb');
        if ($source === null || $destination === false) {
            if (is_resource($source)) {
                fclose($source);
            }
            if (is_resource($destination)) {
                fclose($destination);
            }

            throw new RuntimeException('The restore artifact could not be staged.');
        }

        try {
            if (stream_copy_to_stream($source, $destination) === false) {
                throw new RuntimeException('The restore artifact could not be staged.');
            }
        } finally {
            fclose($source);
            fclose($destination);
        }

        if (! hash_equals((string) $target->checksum, (string) hash_file('sha256', $artifact))) {
            throw new RuntimeException('The restore artifact checksum does not match.');
        }

        if ($target->type === BackupType::Database) {
            return [
                'database' => $this->decompressDatabase($artifact, $workingDirectory),
                'uploads' => null,
                'upload_files' => 0,
            ];
        }

        return $this->prepareFullArchive($artifact, $workingDirectory, $this->validatedManifest($target));
    }

    private function decompressDatabase(string $artifact, string $workingDirectory): string
    {
        $database = $workingDirectory.'/database.sql';
        $input = gzopen($artifact, 'rb');
        $output = fopen($database, 'wb');
        if ($input === false || $output === false) {
            if (is_resource($input)) {
                gzclose($input);
            }
            if (is_resource($output)) {
                fclose($output);
            }

            throw new RuntimeException('The database backup is not a valid gzip artifact.');
        }

        $bytes = 0;

        try {
            while (! gzeof($input)) {
                $chunk = gzread($input, 1024 * 1024);
                if ($chunk === false || fwrite($output, $chunk) === false) {
                    throw new RuntimeException('The database backup could not be decompressed.');
                }

                $bytes += strlen($chunk);
                $this->assertWithinRestoreSizeLimit($bytes);
            }
        } finally {
            gzclose($input);
            fclose($output);
        }

        if (! File::isFile($database) || File::size($database) === 0) {
            throw new RuntimeException('The database backup is empty.');
        }

        return $database;
    }

    /**
     * @param  array<string, mixed>  $expectedManifest
     * @return array{database: string, uploads: string, upload_files: int}
     */
    private function prepareFullArchive(string $artifact, string $workingDirectory, array $expectedManifest): array
    {
        try {
            $archive = new PharData($artifact);
        } catch (Throwable) {
            throw new RuntimeException('The full backup archive is invalid.');
        }

        if (! isset($archive['database.sql'], $archive['manifest.json'])) {
            throw new RuntimeException('The full backup archive is incomplete.');
        }

        if ($archive['manifest.json']->getSize() > 1024 * 1024) {
            throw new RuntimeException('The embedded backup manifest is unexpectedly large.');
        }

        $embeddedManifest = json_decode($archive['manifest.json']->getContent(), true, flags: JSON_THROW_ON_ERROR);
        if ($this->canonicalize($embeddedManifest) !== $this->canonicalize($expectedManifest)) {
            throw new RuntimeException('The embedded backup manifest does not match its record.');
        }

        $database = $workingDirectory.'/database.sql';
        $totalBytes = (int) $archive['database.sql']->getSize();
        $this->assertWithinRestoreSizeLimit($totalBytes);
        $this->copyFileStream($archive['database.sql']->getPathname(), $database);
        $uploads = $workingDirectory.'/uploads';
        File::ensureDirectoryExists($uploads, 0700);
        $uploadFiles = 0;
        $prefix = 'phar://'.$artifact.'/';

        /** @var SplFileInfo $file */
        foreach (new RecursiveIteratorIterator($archive) as $file) {
            if (! $file->isFile()) {
                continue;
            }

            if ($file->isLink()) {
                throw new RuntimeException('The full backup contains an unsupported symbolic link.');
            }

            $relative = Str::after($file->getPathname(), $prefix);
            $this->assertSafeArchivePath($relative);
            if (! str_starts_with($relative, 'uploads/')) {
                continue;
            }

            $totalBytes += $file->getSize();
            $this->assertWithinRestoreSizeLimit($totalBytes);

            $relativeUpload = Str::after($relative, 'uploads/');
            $destination = $uploads.'/'.$relativeUpload;
            File::ensureDirectoryExists(dirname($destination), 0700);
            $this->copyFileStream($file->getPathname(), $destination);
            $uploadFiles++;
        }

        if (! File::isFile($database) || File::size($database) === 0) {
            throw new RuntimeException('The database backup is empty.');
        }

        return ['database' => $database, 'uploads' => $uploads, 'upload_files' => $uploadFiles];
    }

    private function assertSafeArchivePath(string $path): void
    {
        $segments = preg_split('~/+~', str_replace('\\', '/', $path));
        $allowed = $path === 'database.sql' || $path === 'manifest.json' || str_starts_with($path, 'uploads/');
        if (! $allowed || str_starts_with($path, '/') || $segments === false || in_array('..', $segments, true)) {
            throw new RuntimeException('The full backup contains an unsafe or unsupported path.');
        }
    }

    /**
     * @param  array<mixed>  $value
     * @return array<mixed>
     */
    private function canonicalize(array $value): array
    {
        foreach ($value as $key => $item) {
            if (is_array($item)) {
                $value[$key] = $this->canonicalize($item);
            }
        }

        if (! array_is_list($value)) {
            ksort($value);
        }

        return $value;
    }

    private function copyFileStream(string $sourcePath, string $destinationPath): void
    {
        $source = fopen($sourcePath, 'rb');
        $destination = fopen($destinationPath, 'wb');
        if ($source === false || $destination === false) {
            if (is_resource($source)) {
                fclose($source);
            }
            if (is_resource($destination)) {
                fclose($destination);
            }

            throw new RuntimeException('A backup file could not be staged.');
        }

        try {
            if (stream_copy_to_stream($source, $destination) === false) {
                throw new RuntimeException('A backup file could not be staged.');
            }
        } finally {
            fclose($source);
            fclose($destination);
        }
    }

    private function assertWithinRestoreSizeLimit(int $bytes): void
    {
        $limit = (int) config('backup.restore_max_uncompressed_bytes');
        if ($limit < 1 || $bytes > $limit) {
            throw new RuntimeException('The backup exceeds the configured uncompressed restore size limit.');
        }
    }

    private function replaceUploads(string $stagedUploads, string $workingDirectory): void
    {
        $publicRoot = rtrim(Storage::disk('public')->path(''), DIRECTORY_SEPARATOR);
        $rollbackRoot = $workingDirectory.'/previous-uploads';
        File::ensureDirectoryExists(dirname($publicRoot));

        if (File::isDirectory($publicRoot) && ! rename($publicRoot, $rollbackRoot)) {
            throw new RuntimeException('The existing uploads could not be staged for replacement.');
        }

        if (! rename($stagedUploads, $publicRoot)) {
            if (File::isDirectory($rollbackRoot)) {
                rename($rollbackRoot, $publicRoot);
            }

            throw new RuntimeException('The restored uploads could not be activated.');
        }

        File::deleteDirectory($rollbackRoot);
    }

    /** @return resource */
    private function acquireLock()
    {
        $path = storage_path('app/private/restore.lock');
        File::ensureDirectoryExists(dirname($path), 0700);
        $lock = fopen($path, 'c+');
        if ($lock === false || ! flock($lock, LOCK_EX | LOCK_NB)) {
            if (is_resource($lock)) {
                fclose($lock);
            }

            throw new RuntimeException('Another restore operation is already running.');
        }

        return $lock;
    }

    private function workingDirectory(): string
    {
        return storage_path('app/private/restore-work/'.Str::ulid());
    }

    private function instanceId(): string
    {
        $instanceId = InstanceMetadata::query()->value('instance_id');
        if (! is_string($instanceId) || $instanceId === '') {
            throw new RuntimeException('Instance metadata is required for restore.');
        }

        return $instanceId;
    }

    /** @return array<string, mixed> */
    private function snapshot(BackupRecord $record): array
    {
        return [
            'public_id' => $record->public_id,
            'type' => $record->type->value,
            'status' => $record->status->value,
            'disk' => $record->disk,
            'path' => $record->path,
            'checksum' => $record->checksum,
            'storage_encrypted' => $record->storage_encrypted,
            'size' => $record->size,
            'manifest_json' => $record->manifest_json,
            'failure_message' => null,
            'started_at' => $record->started_at,
            'completed_at' => $record->completed_at,
            'expires_at' => $record->expires_at,
            'verified_at' => $record->verified_at,
        ];
    }

    /** @return array{target: mixed, safety_backup: mixed, instance_id: string} */
    private function logContext(BackupRecord $target, BackupRecord $safetyBackup): array
    {
        return [
            'target' => $target->public_id,
            'safety_backup' => $safetyBackup->public_id,
            'instance_id' => (string) ($target->manifest_json['instance_id'] ?? 'unknown'),
        ];
    }
}
