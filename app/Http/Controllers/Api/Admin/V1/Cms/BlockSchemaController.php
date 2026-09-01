<?php

namespace App\Http\Controllers\Api\Admin\V1\Cms;

use App\Core\Cms\BlockSchemaRegistry;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class BlockSchemaController extends Controller
{
    public function __invoke(BlockSchemaRegistry $schemas): JsonResponse
    {
        return response()->json(['data' => collect($schemas->types())->mapWithKeys(
            fn (string $type): array => [$type => $schemas->get($type)],
        )]);
    }
}
