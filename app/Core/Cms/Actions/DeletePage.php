<?php

namespace App\Core\Cms\Actions;

use App\Core\Audit\AuditRecorder;
use App\Core\Cms\Models\Page;
use App\Core\Cms\PageStatus;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class DeletePage
{
    public function __construct(private readonly AuditRecorder $audit) {}

    /** @return 'archived'|'deleted' */
    public function execute(Page $page, User $actor): string
    {
        if ($page->slug === 'home') {
            throw ValidationException::withMessages(['page' => ['The home page cannot be deleted.']]);
        }

        return DB::transaction(function () use ($page, $actor): string {
            $before = $page->load(['translations', 'blocks'])->toArray();
            if ($page->published_revision_id !== null) {
                $page->update(['status' => PageStatus::Archived, 'updated_by' => $actor->id]);
                $page->increment('revision');
                $this->audit->record('cms.page.archived', $actor, $page, $page->business_id, before: $before, after: $page->fresh()->toArray());

                return 'archived';
            }

            foreach ($page->blocks as $block) {
                $block->usages()->delete();
            }
            $businessId = $page->business_id;
            $this->audit->record('cms.page.deleted', $actor, $page, $businessId, before: $before);
            $page->delete();

            return 'deleted';
        });
    }
}
