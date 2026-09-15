<?php

use App\Http\Controllers\Admin\AlertController;
use App\Http\Controllers\Admin\SurveillanceRuleController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // Only Administrators can create/edit rules (per RolePermissionSeeder).
        Route::middleware('permission:manage surveillance rules')
            ->prefix('rules')
            ->name('rules.')
            ->group(function () {
                Route::get('/', [SurveillanceRuleController::class, 'index'])->name('index');
                Route::get('/create', [SurveillanceRuleController::class, 'create'])->name('create');
                Route::post('/', [SurveillanceRuleController::class, 'store'])->name('store');
                Route::get('/{rule}/edit', [SurveillanceRuleController::class, 'edit'])->name('edit');
                Route::put('/{rule}', [SurveillanceRuleController::class, 'update'])->name('update');
                Route::delete('/{rule}', [SurveillanceRuleController::class, 'destroy'])->name('destroy');
            });

        // Viewing alerts: every role can reach the list/detail (all 4 roles have
        // at least read access per the wireframes). The transition endpoint
        // checks the specific permission per action inline in the controller
        // (acknowledge/confirm/dismiss/resolve vs. the stricter escalate).
        Route::prefix('alerts')->name('alerts.')->group(function () {
            Route::get('/', [AlertController::class, 'index'])->name('index');
            Route::get('/{alert}', [AlertController::class, 'show'])->name('show');
            Route::post('/{alert}/transition', [AlertController::class, 'transition'])->name('transition');

            Route::middleware('permission:manage surveillance rules')
                ->post('/evaluate', [AlertController::class, 'evaluate'])->name('evaluate');
        });
    });
