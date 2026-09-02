<?php

namespace App\Core\Backup\Models;

use App\Core\Backup\BackupStatus;
use App\Core\Backup\BackupType;
use Carbon\CarbonImmutable;
use Database\Factories\Core\Backup\Models\BackupRecordFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property BackupType $type
 * @property BackupStatus $status
 * @property string $disk
 * @property string|null $path
 * @property string|null $checksum
 * @property array<string, mixed>|null $manifest_json
 * @property int|null $size
 * @property CarbonImmutable|null $completed_at
 * @property CarbonImmutable|null $expires_at
 * @property CarbonImmutable|null $started_at
 * @property CarbonImmutable|null $verified_at
 */
#[Fillable(['public_id', 'type', 'status', 'disk', 'path', 'checksum', 'storage_encrypted', 'size', 'manifest_json', 'failure_message', 'started_at', 'completed_at', 'expires_at', 'verified_at'])]
class BackupRecord extends Model
{
    /** @use HasFactory<BackupRecordFactory> */
    use HasFactory;

    protected $hidden = ['failure_message'];

    protected function casts(): array
    {
        return [
            'type' => BackupType::class,
            'status' => BackupStatus::class,
            'storage_encrypted' => 'boolean',
            'manifest_json' => 'array',
            'started_at' => 'immutable_datetime',
            'completed_at' => 'immutable_datetime',
            'expires_at' => 'immutable_datetime',
            'verified_at' => 'immutable_datetime',
        ];
    }
}
