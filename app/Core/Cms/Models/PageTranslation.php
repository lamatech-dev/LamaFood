<?php

namespace App\Core\Cms\Models;

use App\Core\Localization\TranslationState;
use Database\Factories\Core\Cms\Models\PageTranslationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property TranslationState $translation_state
 */
#[Fillable(['page_id', 'locale', 'title', 'meta_title', 'meta_description', 'og_title', 'og_description', 'translation_state'])]
class PageTranslation extends Model
{
    /** @use HasFactory<PageTranslationFactory> */
    use HasFactory;

    /** @return BelongsTo<Page, $this> */
    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    protected function casts(): array
    {
        return ['translation_state' => TranslationState::class];
    }
}
