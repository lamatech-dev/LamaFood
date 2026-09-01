<?php

namespace Tests\Feature\Core\Backup;

use App\Core\Backup\Actions\CreateBackup;
use App\Core\Backup\Actions\VerifyBackup;
use App\Core\Backup\BackupStatus;
use App\Core\Backup\BackupType;
use App\Core\Backup\Contracts\DatabaseDumper;
use App\Core\Backup\Models\BackupRecord;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PharData;
use Tests\TestCase;

class BackupLifecycleTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_full_backup_contains_database_uploads_and_non_secret_recovery_manifest(): void
    {
        Storage::fake('backups');
        Storage::fake('public');
        Storage::disk('public')->put('media/example.txt', 'media');
        $this->app->bind(DatabaseDumper::class, fn (): DatabaseDumper => new class implements DatabaseDumper
        {
            public function dump(string $destination): void
            {
                file_put_contents($destination, '-- ciphertext-only database dump');
            }
        });

        $record = app(CreateBackup::class)->execute(BackupType::Full);

        $this->assertSame(BackupStatus::Completed, $record->status);
        Storage::disk('backups')->assertExists((string) $record->path);
        $archive = new PharData(Storage::disk('backups')->path((string) $record->path));
        $this->assertTrue(isset($archive['database.sql']));
        $this->assertTrue(isset($archive['uploads/media/example.txt']));
        $manifest = json_decode($archive['manifest.json']->getContent(), true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame('excluded_reprovision_from_external_escrow', $manifest['secret_policy']);
        $this->assertContains('.env', $manifest['excluded_paths']);
        $this->assertStringNotContainsString('APP_KEY=', $archive['manifest.json']->getContent());
    }

    public function test_checksum_verification_marks_completed_backup_as_verified(): void
    {
        Storage::fake('backups');
        Storage::disk('backups')->put('test/database.sql.gz', 'backup');
        $record = BackupRecord::factory()->create();

        $verified = app(VerifyBackup::class)->execute($record);

        $this->assertNotNull($verified->verified_at);
        $this->assertDatabaseHas('audit_logs', ['action' => 'backup.verified']);
    }

    public function test_production_refuses_unencrypted_or_non_external_backup_storage(): void
    {
        Storage::fake('backups');
        $this->app->detectEnvironment(fn (): string => 'production');
        config()->set('backup.storage_encrypted', false);
        config()->set('backup.external_storage', false);

        try {
            app(CreateBackup::class)->execute(BackupType::Database);
            $this->fail('The production backup policy should stop the backup.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Production backups require encrypted external storage.', $exception->getMessage());
            $this->assertDatabaseHas('backup_records', ['status' => 'failed', 'path' => null]);
        } finally {
            $this->app->detectEnvironment(fn (): string => 'testing');
        }
    }
}
