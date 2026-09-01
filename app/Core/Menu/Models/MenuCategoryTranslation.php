<?php

namespace App\Core\Menu\Models;

use App\Core\Localization\TranslationState;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** @property TranslationState $translation_state */
#[Fillable(['category_id', 'locale', 'name', 'description', 'seo_title', 'seo_description', 'translation_state'])]
class MenuCategoryTranslation extends Model
{
    /** @return BelongsTo<MenuCategory, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(MenuCategory::class, 'category_id');
    }

    protected function casts(): array
    {
        return ['translation_state' => TranslationState::class];
    }
}
