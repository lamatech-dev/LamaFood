<?php

namespace App\Core\Menu\Actions;

use App\Core\Audit\AuditRecorder;
use App\Core\Menu\Models\Product;
use App\Core\Menu\PublicationState;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class DeleteProduct
{
    public function __construct(private readonly AuditRecorder $audit) {}

    /** @return 'archived'|'deleted' */
    public function execute(Product $product, User $actor): string
    {
        return DB::transaction(function () use ($product, $actor): string {
            $before = $product->load(['translations', 'branchSettings'])->toArray();
            if ($product->publication_state !== PublicationState::Draft || $product->branchSettings->isNotEmpty()) {
                $product->update(['publication_state' => PublicationState::Archived]);
                $this->audit->record('menu.product.archived', $actor, $product, $product->business_id, before: $before, after: $product->fresh()->toArray());

                return 'archived';
            }

            $this->audit->record('menu.product.deleted', $actor, $product, $product->business_id, before: $before);
            $product->delete();

            return 'deleted';
        });
    }
}
