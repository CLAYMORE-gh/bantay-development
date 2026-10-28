<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;

class ReportExport implements WithMultipleSheets
{
    public function __construct(private array $report)
    {
    }

    public function sheets(): array
    {
        $sheets = [];

        foreach ($this->report['sections'] as $section) {
            $sheets[] = new class($section) implements FromArray, WithTitle {
                public function __construct(private array $section)
                {
                }

                public function array(): array
                {
                    return array_merge([$this->section['columns']], $this->section['rows']);
                }

                public function title(): string
                {
                    // Excel sheet names max 31 chars, no special chars
                    return substr(preg_replace('/[^A-Za-z0-9 ]/', '', $this->section['heading']), 0, 31);
                }
            };
        }

        return $sheets;
    }
}
