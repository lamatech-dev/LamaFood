<?php

namespace Database\Factories\Core\Audit\Models;

use App\Core\Audit\Models\AuditLog;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<AuditLog>
 */
class AuditLogFactory extends Factory
{
    protected $model = AuditLog::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => (string) Str::ulid(),
            'action' => 'foundation.tested',
            'before' => null,
            'after' => null,
            'context' => [],
        ];
    }
}
