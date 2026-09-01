<?php

namespace App\Core\Business\Models;

use App\Core\Menu\Models\ProductBranchSetting;
use Database\Factories\Core\Business\Models\BranchFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['business_id', 'name', 'slug', 'timezone', 'is_default', 'is_active'])]
class Branch extends Model
{
    /** @use HasFactory<BranchFactory> */
    use HasFactory;

    /** @return BelongsTo<Business, $this> */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /** @return HasMany<ProductBranchSetting, $this> */
    public function productSettings(): HasMany
    {
        return $this->hasMany(ProductBranchSetting::class);
    }

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
