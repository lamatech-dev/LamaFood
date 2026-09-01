<?php

namespace App\Core\Cms\Actions;

use App\Core\Audit\AuditRecorder;
use App\Core\Cms\Models\Block;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class DeleteBlock
{
    public function __construct(private readonly AuditRecorder $audit) {}

    public function execute(Block $block, User $actor): void
    {
        DB::transaction(function () use ($block, $actor): void {
            $before = $block->load('translations')->toArray();
            $page = $block->page;
            $position = $block->position;
            $block->usages()->delete();
            $this->audit->record('cms.block.deleted', $actor, $block, $page->business_id, before: $before);
            $block->delete();
            $page->blocks()->where('position', '>', $position)->decrement('position');
            $page->increment('revision');
        });
    }
}
