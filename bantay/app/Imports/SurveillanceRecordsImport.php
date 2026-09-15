<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Expected column headers in the uploaded Excel file (case-insensitive,
 * spaces become underscores automatically via WithHeadingRow):
 *   Disease | ICD10 Code | Barangay | Consultation Date | Age | Sex
 *
 * This class only reads the file — no validation happens here. Validation
 * is handled row-by-row by DatasetValidationService, called from the
 * ProcessDatasetUpload job.
 */
class SurveillanceRecordsImport implements ToCollection, WithHeadingRow
{
    public array $rows = [];

    public function collection($rows)
    {
        $this->rows = $rows->map(fn ($row) => $row->toArray())->toArray();
    }
}
