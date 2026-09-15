<?php

use App\Http\Controllers\Admin\DatasetUploadController;
use Illuminate\Support\Facades\Route;

// Upload requires 'upload datasets'; correction requires 'correct quarantined records'.
// Both roles are already granted these in RolePermissionSeeder (Surveillance Officer).
Route::middleware(['auth'])
    ->prefix('admin/uploads')
    ->name('admin.uploads.')
    ->group(function () {
        Route::middleware('permission:upload datasets')->group(function () {
            Route::get('/', [DatasetUploadController::class, 'index'])->name('index');
            Route::get('/create', [DatasetUploadController::class, 'create'])->name('create');
            Route::post('/', [DatasetUploadController::class, 'store'])->name('store');
            Route::get('/{upload}', [DatasetUploadController::class, 'show'])->name('show');
        });

        Route::middleware('permission:correct quarantined records')->group(function () {
            Route::get('/{upload}/records/{record}/edit', [DatasetUploadController::class, 'editRecord'])->name('records.edit');
            Route::put('/{upload}/records/{record}', [DatasetUploadController::class, 'updateRecord'])->name('records.update');
        });
    });
