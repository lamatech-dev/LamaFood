<?php

namespace App\Http\Controllers\Api\Admin\V1\Menu;

use App\Core\Business\BusinessContextResolver;
use App\Core\Media\Models\Media;
use App\Core\Menu\Actions\CreateProduct;
use App\Core\Menu\Actions\DeleteProduct;
use App\Core\Menu\Actions\ReorderProducts;
use App\Core\Menu\Actions\UpdateProduct;
use App\Core\Menu\Models\MenuCategory;
use App\Core\Menu\Models\Product;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\V1\Menu\ReorderProductsRequest;
use App\Http\Requests\Api\Admin\V1\Menu\StoreProductRequest;
use App\Http\Requests\Api\Admin\V1\Menu\UpdateProductRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->query($request)->with(['translations', 'category.translations', 'branchSettings', 'primaryMedia.translations'])->orderBy('position')->paginate(50)]);
    }

    public function store(StoreProductRequest $request, CreateProduct $create, BusinessContextResolver $contexts): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $business = $contexts->forUser($user);
        $category = MenuCategory::query()->whereBelongsTo($business)->where('public_id', $request->string('category_id'))->firstOrFail();
        $media = $request->filled('primary_media_id')
            ? Media::query()->whereBelongsTo($business)->where('public_id', $request->string('primary_media_id'))->firstOrFail()
            : null;
        $flags = $request->only(['is_featured', 'is_new', 'is_best_seller']);
        /** @var array<string, bool> $flags */
        $product = $create->execute($category, $user, $request->string('slug')->toString(), $request->integer('position'), $request->array('translations'), $flags, $media);

        return response()->json(['data' => $product], 201);
    }

    public function show(Request $request, string $product): JsonResponse
    {
        return response()->json(['data' => $this->find($request, $product)->load(['translations', 'category.translations', 'branchSettings.branch'])]);
    }

    public function update(UpdateProductRequest $request, string $product, UpdateProduct $update, BusinessContextResolver $contexts): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $business = $contexts->forUser($user);
        $category = MenuCategory::query()->whereBelongsTo($business)->where('public_id', $request->string('category_id'))->firstOrFail();
        $media = $request->filled('primary_media_id')
            ? Media::query()->whereBelongsTo($business)->where('public_id', $request->string('primary_media_id'))->firstOrFail()
            : null;
        $flags = $request->only(['is_featured', 'is_new', 'is_best_seller']);
        /** @var array<string, bool> $flags */
        $model = $update->execute($this->find($request, $product), $category, $user, $request->string('slug')->toString(), $request->integer('position'), $request->array('translations'), $flags, $media);

        return response()->json(['data' => $model]);
    }

    public function destroy(Request $request, string $product, DeleteProduct $delete): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return response()->json(['data' => ['result' => $delete->execute($this->find($request, $product), $user)]]);
    }

    public function reorder(ReorderProductsRequest $request, ReorderProducts $reorder, BusinessContextResolver $contexts): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $category = MenuCategory::query()
            ->whereBelongsTo($contexts->forUser($user))
            ->where('public_id', $request->string('category_id'))
            ->firstOrFail();
        $reorder->execute($category, $user, $request->array('products'));

        return response()->json(status: 204);
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
