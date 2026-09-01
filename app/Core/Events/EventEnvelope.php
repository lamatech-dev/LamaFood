<?php

namespace App\Core\Events;

use Carbon\CarbonImmutable;
use Illuminate\Support\Str;

final readonly class EventEnvelope
{
    /** @param array<string, mixed> $payload */
    public function __construct(
        public string $eventId,
        public string $eventName,
        public CarbonImmutable $occurredAt,
        public ?int $actorId,
        public ?int $businessId,
        public ?int $branchId,
        public ?string $subjectType,
        public int|string|null $subjectId,
        public int $payloadVersion,
        public array $payload,
    ) {}

    /** @param array<string, mixed> $payload */
    public static function make(
        string $eventName,
        array $payload = [],
        ?int $actorId = null,
        ?int $businessId = null,
        ?int $branchId = null,
        ?string $subjectType = null,
        int|string|null $subjectId = null,
        int $payloadVersion = 1,
    ): self {
        return new self(
            eventId: (string) Str::ulid(),
            eventName: $eventName,
            occurredAt: CarbonImmutable::now('UTC'),
            actorId: $actorId,
            businessId: $businessId,
            branchId: $branchId,
            subjectType: $subjectType,
            subjectId: $subjectId,
            payloadVersion: $payloadVersion,
            payload: $payload,
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'event_id' => $this->eventId,
            'event_name' => $this->eventName,
            'occurred_at' => $this->occurredAt->toIso8601String(),
            'actor_id' => $this->actorId,
            'business_id' => $this->businessId,
            'branch_id' => $this->branchId,
            'subject' => ['type' => $this->subjectType, 'id' => $this->subjectId],
            'payload_version' => $this->payloadVersion,
            'payload' => $this->payload,
        ];
    }
}
