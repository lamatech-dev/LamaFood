<?php

namespace App\Http\Controllers\Api\Admin\V1\Menu;

use App\Core\Business\BusinessContextResolver;
use App\Core\Menu\Actions\ChangeCategoryPublicationState;
use App\Core\Menu\Actions\CreateMenuCategory;
use App\Core\Menu\Models\MenuCategory;
use App\Core\Menu\PublicationState;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\V1\Menu\ChangeProductStateRequest;
use App\Http\Requests\Api\Admin\V1\Menu\StoreMenuCategoryRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MenuCategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $query = MenuCategory::query()->with('translations')->orderBy('position');
        if (! $user->isGodfather()) {
            $query->where('business_id', $user->business_id);
        }

        return response()->json(['data' => $query->get()]);
    }

    public function store(StoreMenuCategoryRequest $request, CreateMenuCategory $create, BusinessContextResolver $contexts): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $category = $create->execute($contexts->forUser($user), $user, $request->string('slug')->toString(), $request->integer('position'), $request->array('translations'));

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
}
