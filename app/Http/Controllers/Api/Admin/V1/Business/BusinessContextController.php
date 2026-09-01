<?php

namespace App\Http\Controllers\Api\Admin\V1\Business;

use App\Core\Business\BusinessContextResolver;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BusinessContextController extends Controller
{
    public function __invoke(Request $request, BusinessContextResolver $contexts): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $business = $contexts->forUser($user)->load(['locales' => fn ($query) => $query->where('is_enabled', true)->orderByDesc('is_default')->orderBy('id'), 'branches' => fn ($query) => $query->where('is_active', true)->orderByDesc('is_default')]);

        return response()->json(['data' => [
            'business' => $business->only(['name', 'slug', 'default_locale', 'timezone']),
            'locales' => $business->locales->map->only(['locale', 'name', 'native_name', 'direction', 'is_default', 'is_required_for_publication'])->values(),
            'branches' => $business->branches->map->only(['id', 'name', 'slug', 'is_default'])->values(),
        ]]);
    }
}
