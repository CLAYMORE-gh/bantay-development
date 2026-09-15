<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surveillance_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('disease_id')->constrained();

            // absolute: trigger if case count in a period >= threshold_value
            // percentage_increase: trigger if count rose by >= threshold_value% vs previous period
            // consecutive_increase: trigger if count rose for threshold_value consecutive periods
            // geographic_spread: trigger if cases appear in >= threshold_value distinct barangays in a period
            $table->enum('threshold_type', [
                'absolute', 'percentage_increase', 'consecutive_increase', 'geographic_spread',
            ]);
            $table->decimal('threshold_value', 10, 2);

            // Severity produced when this rule triggers.
            $table->enum('severity', ['watch', 'alert', 'critical']);

            // Lower number = higher priority; used to pick the surfaced alert
            // when multiple rules fire for the same disease/period (FR-20).
            $table->unsignedTinyInteger('priority')->default(50);

            $table->date('effective_date');
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['disease_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surveillance_rules');
    }
};
