<?php

namespace App\Core\Menu\Models;

use App\Core\Business\Models\Business;
use App\Core\Media\Models\Media;
use App\Core\Menu\PublicationState;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** @property PublicationState $publication_state */
#[Fillable(['public_id', 'business_id', 'category_id', 'primary_media_id', 'slug', 'publication_state', 'is_featured', 'is_new', 'is_best_seller', 'position'])]
class Product extends Model
{
    /** @return BelongsTo<Business, $this> */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /** @return BelongsTo<MenuCategory, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(MenuCategory::class, 'category_id');
    }

    /** @return BelongsTo<Media, $this> */
    public function primaryMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'primary_media_id');
    }

    /** @return HasMany<ProductTranslation, $this> */
    public function translations(): HasMany
    {
        return $this->hasMany(ProductTranslation::class);
    }

    /** @return HasMany<ProductBranchSetting, $this> */
    public function branchSettings(): HasMany
    {
        return $this->hasMany(ProductBranchSetting::class);
    }

    protected function casts(): array
    {
        return [
            'publication_state' => PublicationState::class,
            'is_featured' => 'boolean',
            'is_new' => 'boolean',
            'is_best_seller' => 'boolean',
            'position' => 'integer',
        ];
    }
}
