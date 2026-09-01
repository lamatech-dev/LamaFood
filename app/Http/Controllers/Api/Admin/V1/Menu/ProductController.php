<?php

namespace App\Http\Controllers\Api\Admin\V1\Menu;

use App\Core\Menu\Actions\CreateProduct;
use App\Core\Menu\Models\MenuCategory;
use App\Core\Menu\Models\Product;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\V1\Menu\StoreProductRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->query($request)->with(['translations', 'category.translations', 'branchSettings'])->orderBy('position')->paginate(50)]);
    }

    public function store(StoreProductRequest $request, CreateProduct $create): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        abort_if($user->business_id === null, 422, 'A business context is required.');
        $category = MenuCategory::query()->where('business_id', $user->business_id)->where('public_id', $request->string('category_id'))->firstOrFail();
        $flags = $request->only(['is_featured', 'is_new', 'is_best_seller']);
        /** @var array<string, bool> $flags */
        $product = $create->execute($category, $user, $request->string('slug')->toString(), $request->integer('position'), $request->array('translations'), $flags);

        return response()->json(['data' => $product], 201);
    }

    public function show(Request $request, string $product): JsonResponse
    {
        return response()->json(['data' => $this->find($request, $product)->load(['translations', 'category.translations', 'branchSettings.branch'])]);
    }

    /** @return Builder<Product> */
    private function query(Request $request): Builder
    {
        /** @var User $user */
        $user = $request->user();
        $query = Product::query();

        return $user->isGodfather() ? $query : $query->where('business_id', $user->business_id);
    }

    private function find(Request $request, string $publicId): Product
    {
        return $this->query($request)->where('public_id', $publicId)->firstOrFail();
    }
}
