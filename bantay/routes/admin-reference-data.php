<?php

// Add this block to routes/web.php (or require it from there).
// Guarded by auth + the 'manage reference data' permission, so only
// Administrators (per the RolePermissionSeeder) can reach these.

use App\Http\Controllers\Admin\BarangayController;
use App\Http\Controllers\Admin\DiseaseController;
use App\Http\Controllers\Admin\Icd10CodeController;
use App\Http\Controllers\Admin\ReportingPeriodController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'permission:manage reference data'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::resource('barangays', BarangayController::class);
        Route::resource('diseases', DiseaseController::class);
        Route::resource('icd10-codes', Icd10CodeController::class);
        Route::resource('reporting-periods', ReportingPeriodController::class);
    });
