<?php

namespace App\Core\Cms\Models;

use App\Core\Localization\TranslationState;
use Database\Factories\Core\Cms\Models\BlockTranslationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['block_id', 'locale', 'content_json', 'translation_state', 'validated_at'])]
class BlockTranslation extends Model
{
    /** @use HasFactory<BlockTranslationFactory> */
    use HasFactory;

    /** @return BelongsTo<Block, $this> */
    public function block(): BelongsTo
    {
        return $this->belongsTo(Block::class);
    }

    protected function casts(): array
    {
        return [
            'content_json' => 'array',
            'translation_state' => TranslationState::class,
            'validated_at' => 'immutable_datetime',
        ];
    }
}
