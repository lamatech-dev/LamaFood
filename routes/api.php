<?php

use App\Http\Controllers\Api\Admin\V1\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Api\Admin\V1\System\HealthController;
use App\Http\Controllers\Api\Admin\V1\System\InstanceMetadataController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin/v1')->group(function (): void {
    Route::post('login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:login')
        ->name('api.admin.v1.auth.login');

    Route::middleware(['auth:sanctum', 'business-permissions'])->group(function (): void {
        Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
            ->name('api.admin.v1.auth.logout');
        Route::get('me', [AuthenticatedSessionController::class, 'show'])
            ->name('api.admin.v1.auth.me');

        Route::get('system/health', HealthController::class)
            ->middleware('permission:system.view')
            ->name('api.admin.v1.system.health');
        Route::get('system/instance-metadata', InstanceMetadataController::class)
            ->middleware('permission:system.view')
            ->name('api.admin.v1.system.instance-metadata');
    });
});
