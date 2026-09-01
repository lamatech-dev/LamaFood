<?php

namespace App\Core\Media\Models;

use Database\Factories\Core\Media\Models\MediaTranslationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['media_id', 'locale', 'alt', 'title', 'caption'])]
class MediaTranslation extends Model
{
    /** @use HasFactory<MediaTranslationFactory> */
    use HasFactory;

    /** @return BelongsTo<Media, $this> */
    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }
}
