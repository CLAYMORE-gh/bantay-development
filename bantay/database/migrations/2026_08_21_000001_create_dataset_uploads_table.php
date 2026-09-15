<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dataset_uploads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporting_period_id')->constrained();
            $table->foreignId('uploaded_by')->constrained('users');
            $table->string('original_filename');
            $table->string('file_hash', 64); // sha256, for file-level duplicate detection
            $table->enum('status', [
                'registered',
                'checking',
                'validating',
                'partially_accepted',
                'quarantined',
                'standardizing',
                'processed',
                'failed',
                'archived',
            ])->default('registered');
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('accepted_rows')->default(0);
            $table->unsignedInteger('quarantined_rows')->default(0);
            $table->text('failure_reason')->nullable();
            $table->timestamps();

            $table->index('file_hash');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dataset_uploads');
    }
};
