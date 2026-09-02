<?php

namespace App\Console\Commands;

use App\Core\Instance\EnsureInstanceMetadata;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('health:scheduler-heartbeat')]
#[Description('Record the application scheduler heartbeat used by V1 health checks')]
class RecordSchedulerHeartbeat extends Command
{
    public function handle(EnsureInstanceMetadata $ensure): int
    {
        $ensure->execute()->update(['last_health_check_at' => now()]);
        $this->components->info('Scheduler heartbeat recorded.');

        return self::SUCCESS;
    }
}
