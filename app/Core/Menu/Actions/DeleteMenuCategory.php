<?php

namespace App\Core\Menu\Actions;

use App\Core\Audit\AuditRecorder;
use App\Core\Menu\Models\MenuCategory;
use App\Core\Menu\PublicationState;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

final class DeleteMenuCategory
{
    public function __construct(private readonly AuditRecorder $audit) {}

    /** @return 'archived'|'deleted' */
    public function execute(MenuCategory $category, User $actor): string
    {
        if ($category->products()->exists() || MenuCategory::query()->where('parent_id', $category->id)->exists()) {
            throw new ConflictHttpException('A category with products or child categories cannot be deleted.');
        }

        return DB::transaction(function () use ($category, $actor): string {
            $before = $category->load('translations')->toArray();
            if ($category->publication_state !== PublicationState::Draft) {
                $category->update(['publication_state' => PublicationState::Archived]);
                $this->audit->record('menu.category.archived', $actor, $category, $category->business_id, before: $before, after: $category->fresh()->toArray());

                return 'archived';
            }
            $this->audit->record('menu.category.deleted', $actor, $category, $category->business_id, before: $before);
            $category->delete();

            return 'deleted';
        });
    }
}
