<?php

use App\Http\Controllers\Api\Admin\V1\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Api\Admin\V1\Cms\PageBlockController;
use App\Http\Controllers\Api\Admin\V1\Cms\PageController;
use App\Http\Controllers\Api\Admin\V1\Cms\PublishedPageController;
use App\Http\Controllers\Api\Admin\V1\Media\MediaController;
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

        Route::get('cms/pages', [PageController::class, 'index'])->middleware('permission:cms.view');
        Route::post('cms/pages', [PageController::class, 'store'])->middleware('permission:cms.edit');
        Route::get('cms/pages/{page}', [PageController::class, 'show'])->middleware('permission:cms.view');
        Route::post('cms/pages/{page}/blocks', [PageBlockController::class, 'store'])->middleware('permission:cms.edit');
        Route::post('cms/pages/{page}/publish', [PublishedPageController::class, 'store'])->middleware('permission:cms.publish');

        Route::get('media', [MediaController::class, 'index'])->middleware('permission:media.view');
        Route::post('media', [MediaController::class, 'store'])->middleware('permission:media.manage');
        Route::patch('media/{media}', [MediaController::class, 'update'])->middleware('permission:media.manage');
        Route::delete('media/{media}', [MediaController::class, 'destroy'])->middleware('permission:media.manage');
    });
});
