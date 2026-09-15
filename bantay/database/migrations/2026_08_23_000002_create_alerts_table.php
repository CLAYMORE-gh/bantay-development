<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surveillance_rule_id')->constrained();
            $table->foreignId('disease_id')->constrained();
            $table->foreignId('barangay_id')->nullable()->constrained(); // null = city-wide
            $table->foreignId('reporting_period_id')->constrained();

            $table->enum('severity', ['watch', 'alert', 'critical']);
            $table->decimal('triggered_value', 10, 2); // the actual measured value that tripped the rule
            $table->decimal('threshold_value', 10, 2); // the rule's threshold at time of triggering

            $table->enum('status', [
                'open', 'acknowledged', 'assigned', 'under_review',
                'confirmed', 'dismissed', 'escalated', 'resolved', 'closed',
            ])->default('open');

            $table->foreignId('acknowledged_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('acknowledged_at')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['disease_id', 'reporting_period_id']);
            $table->index('status');
            $table->index('severity');
            // Prevent duplicate open alerts for the same rule+period+barangay combo.
            $table->unique(
                ['surveillance_rule_id', 'reporting_period_id', 'barangay_id'],
                'alerts_rule_period_barangay_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};
