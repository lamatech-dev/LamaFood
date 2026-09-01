<?php

namespace Tests\Feature\Core\Audit;

use App\Core\Audit\AuditRecorder;
use App\Core\Audit\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use LogicException;
use Tests\TestCase;

class AuditRecorderTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_records_append_only_audit_data_and_redacts_nested_secrets(): void
    {
        $actor = User::factory()->create();

        $audit = app(AuditRecorder::class)->record(
            action: 'foundation.changed',
            actor: $actor,
            subject: $actor,
            after: ['name' => 'Godfather', 'password' => 'plain'],
            context: ['headers' => ['authorization' => 'Bearer secret']],
        );

        $this->assertSame('[REDACTED]', $audit->after['password']);
        $this->assertSame('[REDACTED]', $audit->context['headers']['authorization']);
        $this->assertSame($actor->id, $audit->actor_id);
        $this->assertSame($actor->id, $audit->subject_id);
    }

    public function test_rejects_mutation_of_existing_audit_record(): void
    {
        $audit = AuditLog::factory()->create();

        $this->expectException(LogicException::class);

        $audit->update(['action' => 'changed']);
    }
}
