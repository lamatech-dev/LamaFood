<?php

namespace App\Core\Menu\Models;

use App\Core\Business\Models\Branch;
use App\Core\Menu\AvailabilityState;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** @property AvailabilityState $availability_state */
#[Fillable(['product_id', 'branch_id', 'price_amount', 'availability_state', 'version', 'updated_by'])]
class ProductBranchSetting extends Model
{
    /** @return BelongsTo<Product, $this> */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** @return BelongsTo<Branch, $this> */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /** @return BelongsTo<User, $this> */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    protected function casts(): array
    {
        return ['availability_state' => AvailabilityState::class, 'price_amount' => 'integer', 'version' => 'integer'];
    }
}
