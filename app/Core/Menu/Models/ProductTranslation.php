<?php

namespace App\Core\Menu\Models;

use App\Core\Localization\TranslationState;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** @property TranslationState $translation_state */
#[Fillable(['product_id', 'locale', 'name', 'description', 'ingredients', 'allergen_notice', 'translation_state'])]
class ProductTranslation extends Model
{
    /** @return BelongsTo<Product, $this> */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    protected function casts(): array
    {
        return ['translation_state' => TranslationState::class];
    }
}
