<?php

namespace App\Http\Controllers\Api\Admin\V1\Menu;

use App\Core\Business\BusinessContextResolver;
use App\Core\Menu\Actions\ChangeCategoryPublicationState;
use App\Core\Menu\Actions\CreateMenuCategory;
use App\Core\Menu\Actions\DeleteMenuCategory;
use App\Core\Menu\Actions\ReorderMenuCategories;
use App\Core\Menu\Actions\UpdateMenuCategory;
use App\Core\Menu\Models\MenuCategory;
use App\Core\Menu\PublicationState;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\V1\Menu\ChangeProductStateRequest;
use App\Http\Requests\Api\Admin\V1\Menu\ReorderMenuCategoriesRequest;
use App\Http\Requests\Api\Admin\V1\Menu\StoreMenuCategoryRequest;
use App\Http\Requests\Api\Admin\V1\Menu\UpdateMenuCategoryRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MenuCategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $query = MenuCategory::query()->with(['translations', 'parent.translations'])->withCount(['products', 'children'])->orderBy('position');
        if (! $user->isGodfather()) {
            $query->where('business_id', $user->business_id);
        }

        return response()->json(['data' => $query->get()]);
    }

    public function store(StoreMenuCategoryRequest $request, CreateMenuCategory $create, BusinessContextResolver $contexts): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $business = $contexts->forUser($user);
        $parent = $request->filled('parent_id')
            ? MenuCategory::query()->whereBelongsTo($business)->where('public_id', $request->string('parent_id'))->firstOrFail()
            : null;
        $category = $create->execute($business, $user, $request->string('slug')->toString(), $request->integer('position'), $request->array('translations'), $parent, $request->boolean('is_featured'));

        return response()->json(['data' => $category], 201);
    }

    public function updatePublication(ChangeProductStateRequest $request, string $category, ChangeCategoryPublicationState $change): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $query = MenuCategory::query()->where('public_id', $category);
        if (! $user->isGodfather()) {
            $query->where('business_id', $user->business_id);
        }
        $model = $query->firstOrFail();

        return response()->json(['data' => $change->execute($model, $user, PublicationState::from($request->string('publication_state')->toString()))]);
    }

    public function update(UpdateMenuCategoryRequest $request, string $category, UpdateMenuCategory $update, BusinessContextResolver $contexts): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $business = $contexts->forUser($user);
        $model = MenuCategory::query()->whereBelongsTo($business)->where('public_id', $category)->firstOrFail();
        $parent = $request->filled('parent_id')
            ? MenuCategory::query()->whereBelongsTo($business)->where('public_id', $request->string('parent_id'))->firstOrFail()
            : null;

        return response()->json(['data' => $update->execute($model, $user, $request->string('slug')->toString(), $request->integer('position'), $request->array('translations'), $parent, $request->boolean('is_featured'))]);
    }

    public function destroy(Request $request, string $category, DeleteMenuCategory $delete, BusinessContextResolver $contexts): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $model = MenuCategory::query()->whereBelongsTo($contexts->forUser($user))->where('public_id', $category)->firstOrFail();

        return response()->json(['data' => ['result' => $delete->execute($model, $user)]]);
    }

    public function reorder(ReorderMenuCategoriesRequest $request, ReorderMenuCategories $reorder, BusinessContextResolver $contexts): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $reorder->execute($contexts->forUser($user), $user, $request->array('categories'));

        return response()->json(status: 204);
    }
}
