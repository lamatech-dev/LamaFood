<?php

namespace App\Http\Controllers\Api\Admin\V1\Cms;

use App\Core\Business\BusinessContextResolver;
use App\Core\Cms\Actions\CreatePage;
use App\Core\Cms\Models\Page;
use App\Core\Cms\PageReadiness;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\V1\Cms\StorePageRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function index(Request $request, PageReadiness $readiness): JsonResponse
    {
        $pages = $this->query($request)->with(['translations', 'blocks.translations'])->orderBy('sort_order')->get();

        return response()->json(['data' => $pages->map(fn (Page $page): array => [
            ...$page->toArray(),
            'readiness' => $readiness->report($page),
        ])]);
    }

    public function store(StorePageRequest $request, CreatePage $create, BusinessContextResolver $contexts): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $page = $create->execute($contexts->forUser($user), $user, $request->string('slug')->toString(), $request->string('template', 'standard')->toString(), $request->array('translations'));

        return response()->json(['data' => $page], 201);
    }

    public function show(Request $request, string $page, PageReadiness $readiness): JsonResponse
    {
        $model = $this->find($request, $page)->load(['translations', 'blocks.translations', 'publishedRevision']);

        return response()->json(['data' => [...$model->toArray(), 'readiness' => $readiness->report($model)]]);
    }

    /** @return Builder<Page> */
    private function query(Request $request): Builder
    {
        /** @var User $user */
        $user = $request->user();
        $query = Page::query();

        return $user->isGodfather() ? $query : $query->where('business_id', $user->business_id);
    }

    private function find(Request $request, string $publicId): Page
    {
        return $this->query($request)->where('public_id', $publicId)->firstOrFail();
    }
}
