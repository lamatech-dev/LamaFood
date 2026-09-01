<?php

namespace App\Core\Modules;

use App\Core\Business\Models\Business;
use App\Core\Modules\Models\BundledModuleState;

class SyncBundledModuleStates
{
    public function __construct(private readonly BundledModuleRegistry $registry) {}

    public function execute(Business $business): void
    {
        foreach ($this->registry->all() as $descriptor) {
            BundledModuleState::query()->updateOrCreate(
                ['business_id' => $business->id, 'module_key' => $descriptor->key],
                [
                    'module_version' => $descriptor->version,
                    'schema_version' => $descriptor->schemaVersion,
                    'is_enabled' => $descriptor->key === 'foundation',
                    'configuration' => [],
                ],
            );
        }
    }
}
