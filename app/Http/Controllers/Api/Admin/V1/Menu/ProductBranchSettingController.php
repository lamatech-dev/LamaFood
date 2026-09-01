<?php

namespace App\Http\Controllers\Api\Admin\V1\Menu;

use App\Core\Business\Models\Branch;
use App\Core\Menu\Actions\UpdateProductBranchSetting;
use App\Core\Menu\AvailabilityState;
use App\Core\Menu\Models\Product;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\V1\Menu\UpdateProductBranchSettingRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class ProductBranchSettingController extends Controller
{
    public function update(UpdateProductBranchSettingRequest $request, string $product, string $branch, UpdateProductBranchSetting $update): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $productQuery = Product::query()->where('public_id', $product);
        $branchQuery = Branch::query()->where('id', $branch);
        if (! $user->isGodfather()) {
            abort_if($user->business_id === null, 422, 'A business context is required.');
            $productQuery->where('business_id', $user->business_id);
            $branchQuery->where('business_id', $user->business_id);
        }
        $productModel = $productQuery->firstOrFail();
        $branchModel = $branchQuery->firstOrFail();
        $setting = $update->execute(
            $productModel,
            $branchModel,
            $user,
            $request->integer('price_amount'),
            AvailabilityState::from($request->string('availability_state')->toString()),
            $request->integer('expected_version'),
        );

        return response()->json(['data' => $setting]);
    }
}
