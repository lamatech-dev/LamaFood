<?php

namespace App\Http\Controllers\Api\Admin\V1\System;

use App\Core\Instance\EnsureInstanceMetadata;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class InstanceMetadataController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(EnsureInstanceMetadata $ensure): JsonResponse
    {
        $metadata = $ensure->execute();

        return response()->json([
            'data' => [
                'instance_id' => $metadata->instance_id,
                'license_id' => $metadata->license_id,
                'install_channel' => $metadata->install_channel,
                'core_version' => $metadata->core_version,
                'schema_version' => $metadata->schema_version,
            ],
        ]);
    }
}
