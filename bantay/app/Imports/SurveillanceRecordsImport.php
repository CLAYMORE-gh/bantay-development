<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Result holder + sheet selector for an iClinicSys export.
 *
 * Only the FIRST sheet is read. Exports and re-saved workbooks often carry
 * extra sheets (pivot tables, scratch notes) that can contain personal data
 * and must never be ingested.
 *
 * After Excel::import() the caller reads:
 *   $rows              canonical, whitelisted rows (see IClinicSysMapper)
 *   $discardedColumns  headers that were NOT imported (NAME, DATE OF BIRTH, ...)
 *   $missingRequired   required fields with no matching column
 *   $foundHeaders      every header seen, for error messages
 */
class SurveillanceRecordsImport implements WithMultipleSheets
{
    /** @var array<int, array<string, mixed>> */
    public array $rows = [];

    /** @var string[] */
    public array $discardedColumns = [];

    /** @var string[] */
    public array $missingRequired = [];

    /** @var string[] */
    public array $foundHeaders = [];

    public function sheets(): array
    {
        return [0 => new SurveillanceRecordsSheet($this)];
    }
}
