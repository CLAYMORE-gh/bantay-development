<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surveillance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dataset_upload_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reporting_period_id')->constrained();

            // Standardized/canonical values (nullable until standardization runs)
            $table->foreignId('disease_id')->nullable()->constrained();
            $table->foreignId('icd10_code_id')->nullable()->constrained('icd10_codes');
            $table->foreignId('barangay_id')->nullable()->constrained();

            // Raw values as originally uploaded, kept for audit/correction reference
            $table->json('raw_data');

            $table->date('consultation_date')->nullable();
            $table->unsignedTinyInteger('age')->nullable();
            $table->enum('sex', ['M', 'F'])->nullable();

            // Row-level dedup fingerprint: hash of disease+barangay+date+age+sex
            $table->string('row_fingerprint', 64);

            $table->enum('validation_status', [
                'pending',      // not yet validated
                'valid',        // passed validation, standardized, stored
                'quarantined',  // has at least one Blocking Error
                'corrected',    // was quarantined, has been fixed and revalidated as valid
            ])->default('pending');

            $table->timestamps();

            $table->index('row_fingerprint');
            $table->index('validation_status');
            $table->index(['reporting_period_id', 'disease_id']);
            $table->index(['reporting_period_id', 'barangay_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surveillance_records');
    }
};
