<?php

namespace App\Http\Controllers\Api\Admin\V1\System;

use App\Core\Instance\HealthCheck;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class HealthController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(HealthCheck $health): JsonResponse
    {
        $result = $health->run();

        return response()->json(['data' => $result], $result['status'] === 'ok' ? 200 : 503);
    }
}
