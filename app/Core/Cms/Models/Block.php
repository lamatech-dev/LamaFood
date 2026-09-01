<?php

namespace App\Core\Cms\Models;

use App\Core\Media\Models\MediaUsage;
use App\Models\User;
use Database\Factories\Core\Cms\Models\BlockFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @property array<string, mixed> $structure_json
 */
#[Fillable(['public_id', 'page_id', 'type', 'position', 'is_enabled', 'structure_json', 'schema_version', 'created_by', 'updated_by'])]
class Block extends Model
{
    /** @use HasFactory<BlockFactory> */
    use HasFactory;

    /** @return BelongsTo<Page, $this> */
    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    /** @return HasMany<BlockTranslation, $this> */
    public function translations(): HasMany
    {
        return $this->hasMany(BlockTranslation::class);
    }

    /** @return MorphMany<MediaUsage, $this> */
    public function usages(): MorphMany
    {
        return $this->morphMany(MediaUsage::class, 'subject');
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'is_enabled' => 'boolean',
            'structure_json' => 'array',
            'schema_version' => 'integer',
        ];
    }
}
