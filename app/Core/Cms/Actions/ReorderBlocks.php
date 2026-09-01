<?php

namespace App\Core\Cms\Actions;

use App\Core\Audit\AuditRecorder;
use App\Core\Cms\Models\Page;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ReorderBlocks
{
    public function __construct(private readonly AuditRecorder $audit) {}

    /** @param list<string> $publicIds */
    public function execute(Page $page, User $actor, array $publicIds): Page
    {
        $blocks = $page->blocks()->get();
        $existing = $blocks->pluck('public_id')->sort()->values()->all();
        $requested = collect($publicIds)->sort()->values()->all();
        if ($existing !== $requested) {
            throw ValidationException::withMessages(['blocks' => ['The order must contain every page block exactly once.']]);
        }

        DB::transaction(function () use ($page, $actor, $publicIds, $blocks): void {
            $offset = (int) $blocks->max('position') + count($publicIds) + 1;
            foreach ($blocks as $block) {
                $block->update(['position' => $block->position + $offset]);
            }
            foreach ($publicIds as $position => $publicId) {
                $page->blocks()->where('public_id', $publicId)->update(['position' => $position, 'updated_by' => $actor->id]);
            }
            $page->increment('revision');
            $this->audit->record('cms.blocks.reordered', $actor, $page, $page->business_id, after: ['blocks' => $publicIds]);
        });

        return $page->fresh(['translations', 'blocks.translations', 'publishedRevision']);
    }
}
