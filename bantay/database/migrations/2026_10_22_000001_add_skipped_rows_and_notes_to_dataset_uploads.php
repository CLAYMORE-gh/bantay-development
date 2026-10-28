<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dataset_uploads', function (Blueprint $table) {
            // Rows that are not surveillance cases (no diagnosis, or an ICD-10
            // chapter Z encounter). total_rows = accepted + quarantined + skipped.
            $table->unsignedInteger('skipped_rows')->default(0)->after('quarantined_rows');
            // Human-readable import notes, e.g. which columns were discarded.
            $table->text('notes')->nullable()->after('failure_reason');
        });
    }

    public function down(): void
    {
        Schema::table('dataset_uploads', function (Blueprint $table) {
            $table->dropColumn(['skipped_rows', 'notes']);
        });
    }
};
