<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('system_settings')->insert([
            [
                'key' => 'max_login_attempts',
                'value' => '5',
                'label' => 'Max Login Attempts',
                'description' => 'Failed attempts allowed before temporary lockout. Enforced per email+IP combination.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'lockout_duration_minutes',
                'value' => '1',
                'label' => 'Lockout Duration (minutes, per retry)',
                'description' => 'How long each lockout window lasts. Laravel\'s rate limiter backs off automatically on repeated triggers.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        DB::table('system_settings')->whereIn('key', ['max_login_attempts', 'lockout_duration_minutes'])->delete();
    }
};
