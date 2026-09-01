<?php

namespace App\Core\Instance;

use App\Core\Instance\Models\InstanceMetadata;
use Illuminate\Support\Str;

class EnsureInstanceMetadata
{
    public function execute(): InstanceMetadata
    {
        $metadata = InstanceMetadata::query()->first();

        if ($metadata !== null) {
            return $metadata;
        }

        return InstanceMetadata::query()->create([
            'instance_id' => (string) Str::ulid(),
            'install_channel' => 'managed',
            'core_version' => '0.1.0',
            'schema_version' => 1,
            'metadata' => [],
        ]);
    }
}
