<?php

namespace App\Core\Instance;

use Illuminate\Support\Facades\DB;
use Throwable;

class HealthCheck
{
    /** @return array{status: string, checks: array{application: string, database: string}} */
    public function run(): array
    {
        $database = 'ok';

        try {
            DB::select('SELECT 1');
        } catch (Throwable) {
            $database = 'failed';
        }

        return [
            'status' => $database === 'ok' ? 'ok' : 'degraded',
            'checks' => [
                'application' => 'ok',
                'database' => $database,
            ],
        ];
    }
}
