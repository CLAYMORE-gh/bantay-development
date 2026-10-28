<?php

namespace App\Imports;

use App\Support\IClinicSysMapper;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class SurveillanceRecordsSheet implements ToCollection
{
    public function __construct(private SurveillanceRecordsImport $result)
    {
    }

    public function collection(Collection $rows): void
    {
        $rows = $rows->values();

        // The header row is the first row with at least two non-empty cells,
        // so a title line or blank rows above it don't break the import.
        $headerIndex = null;
        foreach ($rows as $i => $row) {
            $filled = collect($row)->filter(fn ($v) => $v !== null && trim((string) $v) !== '')->count();
            if ($filled >= 2) {
                $headerIndex = $i;
                break;
            }
        }

        if ($headerIndex === null) {
            $this->result->missingRequired = IClinicSysMapper::REQUIRED;

            return;
        }

        $map = IClinicSysMapper::mapHeaders($rows[$headerIndex]->all());

        $this->result->discardedColumns = $map['discarded'];
        $this->result->missingRequired = $map['missing'];
        $this->result->foundHeaders = $map['found'];

        if ($map['missing'] !== []) {
            return; // the job reports this as a clear failure instead of quarantining every row
        }

        foreach ($rows->slice($headerIndex + 1) as $i => $row) {
            $cells = $row->all();

            // Only whitelisted columns are ever copied out of the sheet.
            // 'source_row' is the Excel row number, so a reviewer can find it.
            $record = ['source_row' => $i + 1];
            $hasData = false;

            foreach ($map['fields'] as $col => $field) {
                $value = $cells[$col] ?? null;

                if (is_string($value)) {
                    $value = trim($value);
                }
                if ($value === '') {
                    $value = null;
                }

                if ($field === 'consultation_date' && $value !== null) {
                    // Excel dates arrive as serial numbers (e.g. 46267). Keep the
                    // original text if it can't be parsed so validation can flag it.
                    $value = IClinicSysMapper::parseDate($value) ?? (string) $value;
                }

                if ($value !== null) {
                    $hasData = true;
                }

                $record[$field] = $value;
            }

            if ($hasData) {
                $this->result->rows[] = $record;
            }
        }
    }
}
