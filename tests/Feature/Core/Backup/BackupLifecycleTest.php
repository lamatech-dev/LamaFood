<?php

namespace Tests\Feature\Core\Backup;

use App\Core\Backup\Actions\CreateBackup;
use App\Core\Backup\Actions\RestoreBackup;
use App\Core\Backup\Actions\VerifyBackup;
use App\Core\Backup\BackupStatus;
use App\Core\Backup\BackupType;
use App\Core\Backup\Contracts\DatabaseDumper;
use App\Core\Backup\Contracts\DatabaseRestorer;
use App\Core\Backup\Models\BackupRecord;
use App\Core\Instance\Models\InstanceMetadata;
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

    public function test_restore_preflight_validates_and_stages_a_database_backup_without_mutation(): void
    {
        Storage::fake('backups');
        $instance = InstanceMetadata::factory()->create();
        $targetArtifact = gzencode('-- restorable database');
        $this->assertIsString($targetArtifact);
        Storage::disk('backups')->put('target.sql.gz', $targetArtifact);
        Storage::disk('backups')->put('safety.tar.gz', 'verified safety artifact');
        $target = BackupRecord::factory()->create([
            'path' => 'target.sql.gz',
            'checksum' => hash('sha256', $targetArtifact),
            'manifest_json' => $this->manifest($instance, BackupType::Database),
            'completed_at' => now()->subHour(),
            'verified_at' => now()->subHour(),
        ]);
        $safety = BackupRecord::factory()->create([
            'type' => BackupType::PreRelease,
            'path' => 'safety.tar.gz',
            'checksum' => hash('sha256', 'verified safety artifact'),
            'manifest_json' => $this->manifest($instance, BackupType::PreRelease),
            'completed_at' => now(),
            'verified_at' => now(),
        ]);
        $restorer = new class implements DatabaseRestorer
        {
            public function restore(string $source): void
            {
                throw new \LogicException('Preflight must not invoke the database restorer.');
            }
        };
        $this->app->instance(DatabaseRestorer::class, $restorer);

        $plan = app(RestoreBackup::class)->preflight($target, $safety);

        $this->assertSame($target->public_id, $plan['target']);
        $this->assertSame(strlen('-- restorable database'), $plan['database_bytes']);
        $this->assertSame(0, $plan['upload_files']);
        $this->assertDatabaseMissing('audit_logs', ['action' => 'backup.restore.started']);
    }

    public function test_restore_execution_uses_staged_sql_and_records_audit_without_exposing_credentials(): void
    {
        Storage::fake('backups');
        $instance = InstanceMetadata::factory()->create();
        $targetArtifact = gzencode('-- controlled restore');
        $this->assertIsString($targetArtifact);
        Storage::disk('backups')->put('target.sql.gz', $targetArtifact);
        Storage::disk('backups')->put('safety.tar.gz', 'verified safety artifact');
        $target = BackupRecord::factory()->create([
            'path' => 'target.sql.gz',
            'checksum' => hash('sha256', $targetArtifact),
            'manifest_json' => $this->manifest($instance, BackupType::Database),
            'completed_at' => now()->subHour(),
            'verified_at' => now()->subHour(),
        ]);
        $safety = BackupRecord::factory()->create([
            'type' => BackupType::PreRelease,
            'path' => 'safety.tar.gz',
            'checksum' => hash('sha256', 'verified safety artifact'),
            'manifest_json' => $this->manifest($instance, BackupType::PreRelease),
            'completed_at' => now(),
            'verified_at' => now(),
        ]);
        $restorer = new class implements DatabaseRestorer
        {
            public ?string $sql = null;

            public function restore(string $source): void
            {
                $contents = file_get_contents($source);
                $this->sql = is_string($contents) ? $contents : null;
            }
        };
        $this->app->instance(DatabaseRestorer::class, $restorer);

        app()->maintenanceMode()->activate([]);

        try {
            app(RestoreBackup::class)->execute($target, $safety);
        } finally {
            app()->maintenanceMode()->deactivate();
        }

        $this->assertSame('-- controlled restore', $restorer->sql);
        $this->assertDatabaseHas('audit_logs', ['action' => 'backup.restore.completed']);
        $this->assertDatabaseHas('backup_records', ['public_id' => $safety->public_id]);
    }

    public function test_full_restore_preflight_validates_embedded_manifest_and_upload_paths(): void
    {
        Storage::fake('backups');
        Storage::fake('public');
        Storage::disk('public')->put('media/example.txt', 'restorable media');
        InstanceMetadata::factory()->create();
        $this->app->bind(DatabaseDumper::class, fn (): DatabaseDumper => new class implements DatabaseDumper
        {
            public function dump(string $destination): void
            {
                file_put_contents($destination, '-- full restore database');
            }
        });
        $target = app(CreateBackup::class)->execute(BackupType::Full);
        $target = app(VerifyBackup::class)->execute($target);
        $targetArchive = new PharData(Storage::disk('backups')->path((string) $target->path));
        $embeddedManifest = json_decode($targetArchive['manifest.json']->getContent(), true, flags: JSON_THROW_ON_ERROR);
        $this->assertEquals($target->manifest_json, $embeddedManifest);
        $target->update(['completed_at' => now()->subHour()]);
        $safety = app(CreateBackup::class)->execute(BackupType::PreRelease);
        $safety = app(VerifyBackup::class)->execute($safety);

        $plan = app(RestoreBackup::class)->preflight($target->fresh(), $safety);

        $this->assertSame(1, $plan['upload_files']);
        $this->assertSame(strlen('-- full restore database'), $plan['database_bytes']);
        Storage::disk('public')->assertExists('media/example.txt');
    }

    public function test_restore_rejects_an_unverified_or_tampered_artifact(): void
    {
        Storage::fake('backups');
        $instance = InstanceMetadata::factory()->create();
        Storage::disk('backups')->put('target.sql.gz', 'tampered');
        Storage::disk('backups')->put('safety.tar.gz', 'verified safety artifact');
        $target = BackupRecord::factory()->create([
            'path' => 'target.sql.gz',
            'checksum' => hash('sha256', 'expected'),
            'manifest_json' => $this->manifest($instance, BackupType::Database),
            'completed_at' => now()->subHour(),
            'verified_at' => now()->subHour(),
        ]);
        $safety = BackupRecord::factory()->create([
            'type' => BackupType::PreRelease,
            'path' => 'safety.tar.gz',
            'checksum' => hash('sha256', 'verified safety artifact'),
            'manifest_json' => $this->manifest($instance, BackupType::PreRelease),
            'completed_at' => now(),
            'verified_at' => now(),
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('checksum does not match');

        app(RestoreBackup::class)->preflight($target, $safety);
    }

    public function test_restore_command_requires_maintenance_mode_before_execution(): void
    {
        $instance = InstanceMetadata::factory()->create();
        $target = BackupRecord::factory()->create(['completed_at' => now()->subHour()]);
        $safety = BackupRecord::factory()->create([
            'type' => BackupType::PreRelease,
            'completed_at' => now(),
        ]);
        $this->mock(RestoreBackup::class, function ($mock) use ($instance, $safety, $target): void {
            $mock->shouldReceive('preflight')->once()->andReturn([
                'target' => $target->public_id,
                'safety_backup' => $safety->public_id,
                'type' => BackupType::Database->value,
                'instance_id' => $instance->instance_id,
                'database_bytes' => 20,
                'upload_files' => 0,
            ]);
            $mock->shouldNotReceive('execute');
        });
        app()->maintenanceMode()->deactivate();

        $this->artisan('backup:restore', [
            'public_id' => $target->public_id,
            '--safety-backup' => $safety->public_id,
            '--execute' => true,
            '--confirmation' => "RESTORE {$target->public_id} ON {$instance->instance_id}",
        ])->expectsOutputToContain('requires application maintenance mode')->assertFailed();
    }

    public function test_restore_command_requires_the_exact_instance_confirmation_phrase(): void
    {
        $instance = InstanceMetadata::factory()->create();
        $target = BackupRecord::factory()->create(['completed_at' => now()->subHour()]);
        $safety = BackupRecord::factory()->create([
            'type' => BackupType::PreRelease,
            'completed_at' => now(),
        ]);
        $this->mock(RestoreBackup::class, function ($mock) use ($instance, $safety, $target): void {
            $mock->shouldReceive('preflight')->once()->andReturn([
                'target' => $target->public_id,
                'safety_backup' => $safety->public_id,
                'type' => BackupType::Database->value,
                'instance_id' => $instance->instance_id,
                'database_bytes' => 20,
                'upload_files' => 0,
            ]);
            $mock->shouldNotReceive('execute');
        });
        app()->maintenanceMode()->activate([]);

        try {
            $this->artisan('backup:restore', [
                'public_id' => $target->public_id,
                '--safety-backup' => $safety->public_id,
                '--execute' => true,
                '--confirmation' => 'RESTORE THE WRONG TARGET',
            ])->expectsOutputToContain('confirmation phrase is invalid')->assertExitCode(2);
        } finally {
            app()->maintenanceMode()->deactivate();
        }
    }

    /** @return array<string, mixed> */
    private function manifest(InstanceMetadata $instance, BackupType $type): array
    {
        return [
            'format_version' => 1,
            'type' => $type->value,
            'created_at' => now()->toAtomString(),
            'instance_id' => $instance->instance_id,
            'core_version' => $instance->core_version,
            'secret_policy' => 'excluded_reprovision_from_external_escrow',
            'required_secret_references' => config('backup.required_secret_references', []),
            'excluded_paths' => config('backup.excluded_paths', []),
            'contents' => $type === BackupType::Database ? ['database.sql'] : ['database.sql', 'uploads/'],
        ];
    }
}
