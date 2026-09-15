<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('validation_findings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surveillance_record_id')->constrained()->cascadeOnDelete();
            $table->string('field');           // e.g. 'icd10_code', 'barangay', 'consultation_date'
            $table->enum('severity', ['informational', 'warning', 'blocking']);
            $table->string('message');
            $table->boolean('resolved')->default(false);
            $table->timestamps();

            $table->index('severity');
            $table->index('resolved');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('validation_findings');
    }
};
