<?php

namespace App\Core\Audit;

use App\Core\Audit\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AuditRecorder
{
    public function __construct(private readonly RedactsSensitiveValues $redactor) {}

    /**
     * @param  array<string, mixed>|null  $before
     * @param  array<string, mixed>|null  $after
     * @param  array<string, mixed>  $context
     */
    public function record(
        string $action,
        ?Model $actor = null,
        ?Model $subject = null,
        ?int $businessId = null,
        ?int $branchId = null,
        ?array $before = null,
        ?array $after = null,
        array $context = [],
    ): AuditLog {
        return AuditLog::query()->create([
            'event_id' => (string) Str::ulid(),
            'business_id' => $businessId,
            'branch_id' => $branchId,
            'actor_type' => $actor?->getMorphClass(),
            'actor_id' => $actor?->getKey(),
            'action' => $action,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'before' => $before === null ? null : $this->redactor->handle($before),
            'after' => $after === null ? null : $this->redactor->handle($after),
            'context' => $this->redactor->handle($context),
        ]);
    }
}
