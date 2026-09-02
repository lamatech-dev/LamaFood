<?php

namespace App\Core\Instance;

use App\Core\Backup\BackupStatus;
use App\Core\Backup\Models\BackupRecord;
use App\Core\Instance\Models\InstanceMetadata;
use Carbon\CarbonImmutable;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class HealthCheck
{
    /** @return array{status: string, checks: array{application: string, database: string, storage: string, queue: string, scheduler: string, backup: string, backup_freshness: string}} */
    public function run(): array
    {
        $checks = [
            'application' => 'ok',
            'database' => $this->database(),
            'storage' => $this->storage(),
            'queue' => $this->queue(),
            'scheduler' => $this->scheduler(),
            'backup' => $this->backup(),
        ];
        $checks['backup_freshness'] = $checks['backup'];
        $required = ['application', 'database', 'storage', 'queue'];

        if (app()->isProduction()) {
            $required = [...$required, 'scheduler', 'backup_freshness'];
        }

        $healthy = collect($required)->every(fn (string $check): bool => $checks[$check] === 'ok');

        return [
            'status' => $healthy ? 'ok' : 'degraded',
            'checks' => $checks,
        ];
    }

    private function database(): string
    {
        try {
            DB::select('SELECT 1');

            return 'ok';
        } catch (Throwable) {
            return 'failed';
        }
    }

    private function storage(): string
    {
        $path = 'health/'.Str::ulid().'.check';
        $disk = null;

        try {
            $disk = Storage::disk((string) config('health.storage_disk'));
            $written = $disk->put($path, 'ok');

            return $written && $disk->exists($path) ? 'ok' : 'failed';
        } catch (Throwable) {
            return 'failed';
        } finally {
            $this->deleteHealthFile($disk, $path);
        }
    }

    private function queue(): string
    {
        try {
            Queue::connection((string) config('queue.default'))->size();

            return 'ok';
        } catch (Throwable) {
            return 'failed';
        }
    }

    private function scheduler(): string
    {
        try {
            $heartbeat = InstanceMetadata::query()->latest('last_health_check_at')->value('last_health_check_at');

            return match (true) {
                $heartbeat === null => 'missing',
                CarbonImmutable::parse($heartbeat)->isBefore(now()->subMinutes((int) config('health.scheduler_stale_after_minutes'))) => 'stale',
                default => 'ok',
            };
        } catch (Throwable) {
            return 'failed';
        }
    }

    private function backup(): string
    {
        try {
            $lastBackup = BackupRecord::query()->where('status', BackupStatus::Completed)->latest('completed_at')->first();

            return match (true) {
                $lastBackup === null => 'missing',
                $lastBackup->completed_at?->isBefore(now()->subHours((int) config('health.backup_stale_after_hours'))) === true => 'stale',
                $lastBackup->verified_at === null => 'unverified',
                default => 'ok',
            };
        } catch (Throwable) {
            return 'failed';
        }
    }

    private function deleteHealthFile(?FilesystemAdapter $disk, string $path): void
    {
        if ($disk === null) {
            return;
        }

        try {
            $disk->delete($path);
        } catch (Throwable) {
            // The primary storage status already captures adapter failures.
        }
    }
}
