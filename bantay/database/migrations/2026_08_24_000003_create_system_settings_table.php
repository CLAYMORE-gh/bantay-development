<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('label');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Seed sensible defaults so the settings page isn't empty on first visit.
        DB::table('system_settings')->insert([
            ['key' => 'app_name', 'value' => 'BANTAY', 'label' => 'Application Name', 'description' => 'Shown in the sidebar and page titles.', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'data_retention_days', 'value' => '1825', 'label' => 'Data Retention Period (days)', 'description' => 'How long surveillance records are kept before archival. Default: 5 years.', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'alert_escalation_hours', 'value' => '48', 'label' => 'Auto-Escalation Threshold (hours)', 'description' => 'Unresolved Critical alerts older than this may be flagged for escalation.', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'session_timeout_minutes', 'value' => '30', 'label' => 'Session Timeout (minutes)', 'description' => 'How long an inactive session stays logged in.', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
