<?php

namespace App\Http\Controllers\Api\Admin\V1\Menu;

use App\Core\Menu\Actions\ChangeProductPublicationState;
use App\Core\Menu\Models\Product;
use App\Core\Menu\PublicationState;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\V1\Menu\ChangeProductStateRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class ProductPublicationController extends Controller
{
    public function update(ChangeProductStateRequest $request, string $product, ChangeProductPublicationState $change): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $query = Product::query()->where('public_id', $product);
        if (! $user->isGodfather()) {
            $query->where('business_id', $user->business_id);
        }
        $model = $query->firstOrFail();
        $state = PublicationState::from($request->string('publication_state')->toString());

        return response()->json(['data' => $change->execute($model, $user, $state)]);
    }
}
