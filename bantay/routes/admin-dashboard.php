<?php

use App\Http\Controllers\Admin\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::middleware('permission:view dashboards')
            ->get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');

        Route::middleware('permission:view geographic map')
            ->get('/dashboard/map', [DashboardController::class, 'geographic'])
            ->name('dashboard.map');
    });
