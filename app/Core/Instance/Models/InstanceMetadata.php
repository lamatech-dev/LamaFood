<?php

namespace App\Core\Instance\Models;

use App\Core\Business\Models\Business;
use Database\Factories\Core\Instance\Models\InstanceMetadataFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['instance_id', 'business_id', 'license_id', 'install_channel', 'core_version', 'schema_version', 'last_health_check_at', 'metadata'])]
class InstanceMetadata extends Model
{
    /** @use HasFactory<InstanceMetadataFactory> */
    use HasFactory;

    /** @return BelongsTo<Business, $this> */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    protected function casts(): array
    {
        return [
            'schema_version' => 'integer',
            'last_health_check_at' => 'immutable_datetime',
            'metadata' => 'array',
        ];
    }
}
