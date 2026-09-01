<?php

namespace App\Core\Menu\Actions;

use App\Core\Audit\AuditRecorder;
use App\Core\Business\Models\Branch;
use App\Core\Menu\AvailabilityState;
use App\Core\Menu\Models\Product;
use App\Core\Menu\Models\ProductBranchSetting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class UpdateProductBranchSetting
{
    public function __construct(private readonly AuditRecorder $audit) {}

    public function execute(Product $product, Branch $branch, User $actor, int $priceAmount, AvailabilityState $availability, int $expectedVersion): ProductBranchSetting
    {
        abort_unless($product->business_id === $branch->business_id, 422, 'Product and branch must belong to the same business.');

        return DB::transaction(function () use ($product, $branch, $actor, $priceAmount, $availability, $expectedVersion): ProductBranchSetting {
            $setting = ProductBranchSetting::query()->whereBelongsTo($product)->whereBelongsTo($branch)->lockForUpdate()->first();
            if ($setting === null) {
                if ($expectedVersion !== 0) {
                    throw new ConflictHttpException('Branch setting does not exist at the expected version.');
                }
                $setting = new ProductBranchSetting(['product_id' => $product->id, 'branch_id' => $branch->id, 'version' => 0]);
            } elseif ($setting->version !== $expectedVersion) {
                throw new ConflictHttpException('Branch setting changed after it was loaded.');
            }

            $before = $setting->exists ? $setting->toArray() : null;
            $setting->fill([
                'price_amount' => $priceAmount,
                'availability_state' => $availability,
                'version' => $expectedVersion + 1,
                'updated_by' => $actor->id,
            ])->save();
            $this->audit->record('menu.product.branch_setting_changed', $actor, $setting, $product->business_id, $branch->id, before: $before, after: $setting->toArray());

            return $setting->fresh() ?? $setting;
        });
    }
}
