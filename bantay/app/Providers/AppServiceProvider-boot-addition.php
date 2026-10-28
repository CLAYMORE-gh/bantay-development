<?php

/**
 * NOT a standalone file — this is the code to ADD to your existing
 * app/Providers/AppServiceProvider.php's boot() method. See the README
 * for exact placement instructions.
 */

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Schema;

// Apply the Session Timeout value from System Settings to Laravel's actual
// session lifetime, so changing it in /admin/settings takes real effect —
// not just a cosmetic field. Wrapped in a try/catch + table-exists check
// because this runs on EVERY request including early artisan commands
// (like migrate itself) before the system_settings table exists yet.
if (Schema::hasTable('system_settings')) {
    try {
        $timeoutMinutes = SystemSetting::get('session_timeout_minutes');
        if ($timeoutMinutes) {
            config(['session.lifetime' => (int) $timeoutMinutes]);
        }
    } catch (\Throwable $e) {
        // Fails silently — falls back to .env's SESSION_LIFETIME. Never let
        // a settings-table hiccup break every page load.
    }
}
