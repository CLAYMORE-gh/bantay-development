<?php

namespace App\Providers;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
{
    // Apply the Session Timeout from System Settings to Laravel's session
    // lifetime. The whole block is guarded: this runs on every boot,
    // including composer/artisan commands on a fresh machine where the
    // driver, database or table may not exist yet.
    try {
        if (Schema::hasTable('system_settings')) {
            $timeoutMinutes = SystemSetting::get('session_timeout_minutes');
            if ($timeoutMinutes) {
                config(['session.lifetime' => (int) $timeoutMinutes]);
            }
        }
    } catch (\Throwable $e) {
        // Falls back to .env's SESSION_LIFETIME.
    }
}
}