<?php

namespace Database\Seeders;

use App\Models\Disease;
use App\Models\Icd10Code;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

/**
 * DRAFT reference data built from the ICD-10 codes and diagnosis labels that
 * actually appear in the City Health Office's iClinicSys export
 * (SEPTEMBER-2026-SOUTH.xlsx).
 *
 *  - code + description come straight from the export (nothing invented).
 *  - the "disease" grouping in data/iclinicsys_icd10_draft.csv is a SUGGESTION
 *    and must be reviewed/approved by the City Health Office. Edit the CSV
 *    (or the Disease column in /admin/icd10-codes) and re-run: existing codes
 *    are updated, nothing is duplicated.
 *  - codes that start with Z (non-disease encounters) are intentionally not
 *    loaded; the importer skips them.
 */
class IClinicSysReferenceSeeder extends Seeder
{
    public function run(): void
    {
        $handle = fopen(__DIR__ . '/data/iclinicsys_icd10_draft.csv', 'r');
        $header = fgetcsv($handle);

        $codes = 0;
        $diseases = 0;

        DB::transaction(function () use ($handle, $header, &$codes, &$diseases) {
            while (($row = fgetcsv($handle)) !== false) {
                if ($row === [null] || count($row) !== count($header)) {
                    continue;
                }
                $r = array_combine($header, $row);

                $disease = Disease::firstOrCreate(['name' => $r['disease']], ['is_active' => true]);
                $diseases += $disease->wasRecentlyCreated ? 1 : 0;

                Icd10Code::updateOrCreate(
                    ['code' => $r['code']],
                    ['description' => $r['description'], 'disease_id' => $disease->id, 'is_active' => true]
                );
                $codes++;
            }
        });
        fclose($handle);

        $this->command?->info("ICD-10 codes loaded/updated: {$codes}. New diseases created: {$diseases}.");
    }
}
