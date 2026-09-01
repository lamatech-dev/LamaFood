<?php

namespace App\Core\Cms\Actions;

use App\Core\Audit\AuditRecorder;
use App\Core\Cms\BlockSnapshotBuilder;
use App\Core\Cms\Models\Page;
use App\Core\Cms\Models\PageRevision;
use App\Core\Cms\PageReadiness;
use App\Core\Cms\PageStatus;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class PublishPage
{
    public function __construct(
        private readonly PageReadiness $readiness,
        private readonly AuditRecorder $audit,
        private readonly BlockSnapshotBuilder $blocks,
    ) {}

    public function execute(Page $page, User $actor, int $expectedRevision): PageRevision
    {
        return DB::transaction(function () use ($page, $actor, $expectedRevision): PageRevision {
            /** @var Page $locked */
            $locked = Page::query()->lockForUpdate()->findOrFail($page->id);
            if ($locked->revision !== $expectedRevision) {
                throw new ConflictHttpException('The page changed after it was loaded. Refresh and try again.');
            }

            $report = $this->readiness->report($locked);
            if (! $report['ready']) {
                throw ValidationException::withMessages(['readiness' => [json_encode($report, JSON_THROW_ON_ERROR)]]);
            }

            $nextRevision = $locked->revision + 1;
            $snapshot = [
                'public_id' => $locked->public_id,
                'slug' => $locked->slug,
                'template' => $locked->template,
                'translations' => $locked->translations->keyBy('locale')->map->only(['title', 'meta_title', 'meta_description', 'og_title', 'og_description'])->all(),
                'blocks' => $locked->blocks->where('is_enabled', true)->map($this->blocks->build(...))->values()->all(),
            ];

            $revision = $locked->revisions()->create([
                'revision' => $nextRevision,
                'snapshot_json' => $snapshot,
                'published_by' => $actor->id,
            ]);
            $locked->update([
                'status' => PageStatus::Published,
                'revision' => $nextRevision,
                'published_revision_id' => $revision->id,
                'published_at' => now(),
                'updated_by' => $actor->id,
            ]);

            $this->audit->record('cms.page.published', $actor, $locked, $locked->business_id, after: ['revision' => $nextRevision]);

            return $revision;
        });
    }
}
