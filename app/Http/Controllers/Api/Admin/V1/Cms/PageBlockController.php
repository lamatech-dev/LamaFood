<?php

namespace App\Http\Controllers\Api\Admin\V1\Cms;

use App\Core\Cms\Actions\CreateBlock;
use App\Core\Cms\Actions\DeleteBlock;
use App\Core\Cms\Actions\ReorderBlocks;
use App\Core\Cms\Actions\UpdateBlock;
use App\Core\Cms\Models\Block;
use App\Core\Cms\Models\Page;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\V1\Cms\ReorderBlocksRequest;
use App\Http\Requests\Api\Admin\V1\Cms\StoreBlockRequest;
use App\Http\Requests\Api\Admin\V1\Cms\UpdateBlockRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

    public function update(UpdateBlockRequest $request, string $page, string $block, UpdateBlock $update): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $model = $this->block($this->page($user, $page), $block);
        $updated = $update->execute($model, $user, $request->string('type')->toString(), $request->boolean('is_enabled'), $request->array('structure'), $request->array('translations'));

        return response()->json(['data' => $updated]);
    }

    public function destroy(Request $request, string $page, string $block, DeleteBlock $delete): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $delete->execute($this->block($this->page($user, $page), $block), $user);

        return response()->json(status: 204);
    }

    public function reorder(ReorderBlocksRequest $request, string $page, ReorderBlocks $reorder): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return response()->json(['data' => $reorder->execute($this->page($user, $page), $user, $request->array('blocks'))]);
    }

    private function page(User $user, string $publicId): Page
    {
        $query = Page::query()->where('public_id', $publicId);
        if (! $user->isGodfather()) {
            $query->where('business_id', $user->business_id);
        }

        return $query->firstOrFail();
    }

    private function block(Page $page, string $publicId): Block
    {
        return $page->blocks()->where('public_id', $publicId)->firstOrFail();
    }
}
