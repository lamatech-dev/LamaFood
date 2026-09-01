<?php

use App\Http\Controllers\Api\Admin\V1\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Api\Admin\V1\Business\BusinessContextController;
use App\Http\Controllers\Api\Admin\V1\Cms\PageBlockController;
use App\Http\Controllers\Api\Admin\V1\Cms\PageController;
use App\Http\Controllers\Api\Admin\V1\Cms\PublishedPageController;
use App\Http\Controllers\Api\Admin\V1\Media\MediaController;
use App\Http\Controllers\Api\Admin\V1\Menu\MenuCategoryController;
use App\Http\Controllers\Api\Admin\V1\Menu\ProductBranchSettingController;
use App\Http\Controllers\Api\Admin\V1\Menu\ProductController;
use App\Http\Controllers\Api\Admin\V1\Menu\ProductPublicationController;
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
        Route::get('business/context', BusinessContextController::class)
            ->middleware('permission:settings.view');

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

        Route::get('categories', [MenuCategoryController::class, 'index'])->middleware('permission:menu.view');
        Route::post('categories', [MenuCategoryController::class, 'store'])->middleware('permission:menu.edit');
        Route::patch('categories/{category}/publication-state', [MenuCategoryController::class, 'updatePublication'])->middleware('permission:menu.publish');
        Route::get('products', [ProductController::class, 'index'])->middleware('permission:menu.view');
        Route::post('products', [ProductController::class, 'store'])->middleware('permission:menu.edit');
        Route::get('products/{product}', [ProductController::class, 'show'])->middleware('permission:menu.view');
        Route::patch('products/{product}/publication-state', [ProductPublicationController::class, 'update'])->middleware('permission:menu.publish');
        Route::put('products/{product}/branches/{branch}/settings', [ProductBranchSettingController::class, 'update'])->middleware('permission:menu.price,menu.availability');
    });
});
