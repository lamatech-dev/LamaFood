<?php

namespace Tests\Unit\Core\Events;

use App\Core\Events\EventEnvelope;
use Tests\TestCase;

class EventEnvelopeTest extends TestCase
{
    public function test_serializes_versioned_event_context_in_a_stable_envelope(): void
    {
        $this->travelTo('2026-09-01 10:00:00 UTC');

        $event = EventEnvelope::make(
            eventName: 'business.created',
            payload: ['name' => 'Denardi'],
            actorId: 7,
            businessId: 8,
            branchId: 9,
            subjectType: 'business',
            subjectId: 8,
            payloadVersion: 2,
        )->toArray();

        $this->assertSame('business.created', $event['event_name']);
        $this->assertSame('2026-09-01T10:00:00+00:00', $event['occurred_at']);
        $this->assertSame(7, $event['actor_id']);
        $this->assertSame(8, $event['business_id']);
        $this->assertSame(9, $event['branch_id']);
        $this->assertSame(['type' => 'business', 'id' => 8], $event['subject']);
        $this->assertSame(2, $event['payload_version']);
        $this->assertSame(['name' => 'Denardi'], $event['payload']);
    }
}
