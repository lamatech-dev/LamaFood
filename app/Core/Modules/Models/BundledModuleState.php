<?php

namespace App\Core\Modules\Models;

use App\Core\Business\Models\Business;
use Database\Factories\Core\Modules\Models\BundledModuleStateFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['business_id', 'module_key', 'module_version', 'schema_version', 'is_enabled', 'configuration'])]
class BundledModuleState extends Model
{
    /** @use HasFactory<BundledModuleStateFactory> */
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
            'is_enabled' => 'boolean',
            'configuration' => 'array',
        ];
    }
}
