<?php

namespace App\Http\Controllers\Api\Admin\V1\Cms;

use App\Core\Cms\Actions\PublishPage;
use App\Core\Cms\Models\Page;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\V1\Cms\PublishPageRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class PublishedPageController extends Controller
{
    public function store(PublishPageRequest $request, string $page, PublishPage $publish): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $query = Page::query()->where('public_id', $page);
        if (! $user->isGodfather()) {
            $query->where('business_id', $user->business_id);
        }
        $revision = $publish->execute($query->firstOrFail(), $user, $request->integer('expected_revision'));

        return response()->json(['data' => $revision], 201);
    }
}
