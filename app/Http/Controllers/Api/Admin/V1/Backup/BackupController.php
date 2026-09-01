<?php

namespace App\Http\Controllers\Api\Admin\V1\Backup;

use App\Core\Backup\Models\BackupRecord;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class BackupController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['data' => BackupRecord::query()->latest('id')->limit(50)->get()]);
    }
}
