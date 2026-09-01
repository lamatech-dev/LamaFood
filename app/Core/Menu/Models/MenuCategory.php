<?php

namespace App\Core\Menu\Models;

use App\Core\Business\Models\Business;
use App\Core\Menu\PublicationState;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** @property PublicationState $publication_state */
#[Fillable(['public_id', 'business_id', 'parent_id', 'slug', 'position', 'publication_state', 'is_featured'])]
class MenuCategory extends Model
{
    /** @return BelongsTo<Business, $this> */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /** @return BelongsTo<MenuCategory, $this> */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /** @return HasMany<MenuCategory, $this> */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /** @return HasMany<MenuCategoryTranslation, $this> */
    public function translations(): HasMany
    {
        return $this->hasMany(MenuCategoryTranslation::class, 'category_id');
    }

    /** @return HasMany<Product, $this> */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    protected function casts(): array
    {
        return ['publication_state' => PublicationState::class, 'is_featured' => 'boolean', 'position' => 'integer'];
    }
}
