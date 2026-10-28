<?php

use App\Http\Controllers\Admin\AuditTrailController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\SystemSettingController;
use App\Http\Controllers\Admin\SystemStatusController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

// Notifications: any authenticated user sees their own.
Route::middleware(['auth'])->prefix('admin/notifications')->name('admin.notifications.')->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->name('index');
    Route::post('/{id}/read', [NotificationController::class, 'markRead'])->name('read');
    Route::post('/mark-all-read', [NotificationController::class, 'markAllRead'])->name('mark-all-read');
});

// Everything else here is Administrator-only, matching every other role's
// wireframe explicitly listing these under "Restricted Functions".
Route::middleware(['auth', 'role:Administrator'])->prefix('admin')->name('admin.')->group(function () {

    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
    });

    Route::get('/system-status', [SystemStatusController::class, 'index'])->name('system-status.index');

    Route::get('/audit-trail', [AuditTrailController::class, 'index'])->name('audit-trail.index');

    Route::prefix('backup')->name('backup.')->group(function () {
        Route::get('/', [BackupController::class, 'index'])->name('index');
        Route::post('/', [BackupController::class, 'create'])->name('create');
        Route::get('/{filename}/download', [BackupController::class, 'download'])->name('download');
        Route::delete('/{filename}', [BackupController::class, 'destroy'])->name('destroy');
        Route::get('/{filename}/restore', [BackupController::class, 'showRestore'])->name('restore.show');
        Route::post('/{filename}/restore', [BackupController::class, 'restore'])->middleware('throttle:5,1')->name('restore');
        
    });

    Route::get('/settings', [SystemSettingController::class, 'index'])->name('settings.index');
    Route::put('/settings', [SystemSettingController::class, 'update'])->name('settings.update');
});

