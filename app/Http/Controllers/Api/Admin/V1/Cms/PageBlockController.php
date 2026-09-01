<?php

namespace App\Http\Controllers\Api\Admin\V1\Cms;

use App\Core\Cms\Actions\CreateBlock;
use App\Core\Cms\Models\Page;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\V1\Cms\StoreBlockRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class PageBlockController extends Controller
{
    public function store(StoreBlockRequest $request, string $page, CreateBlock $create): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $query = Page::query()->where('public_id', $page);
        if (! $user->isGodfather()) {
            $query->where('business_id', $user->business_id);
        }
        $model = $query->firstOrFail();
        $block = $create->execute($model, $user, $request->string('type')->toString(), $request->integer('position'), $request->array('structure'), $request->array('translations'));

        return response()->json(['data' => $block], 201);
    }
}
