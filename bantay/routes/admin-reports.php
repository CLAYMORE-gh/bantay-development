<?php

use App\Http\Controllers\Admin\ReportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'permission:generate reports'])
    ->prefix('admin/reports')
    ->name('admin.reports.')
    ->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/{type}/create', [ReportController::class, 'create'])->name('create');
        Route::get('/{type}', [ReportController::class, 'show'])->name('show');
        Route::get('/{type}/export/pdf', [ReportController::class, 'exportPdf'])->name('export.pdf');
        Route::get('/{type}/export/excel', [ReportController::class, 'exportExcel'])->name('export.excel');
    });
