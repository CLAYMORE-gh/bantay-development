<?php

namespace App\Support;

/**
 * Knows what an iClinicSys OPD export looks like and how to read it safely.
 *
 * - Maps the export's headers (DATE, SITIO/BARANGAY, ICD10 CODE, ...) onto
 *   BANTAY's canonical fields.
 * - Anything NOT on the whitelist (NAME, DATE OF BIRTH, COMPLAINTS,
 *   TREATMENTS, REMARKS, PHIC, ...) is reported as "discarded" and is never
 *   copied into the database. This is the de-identification safeguard.
 * - Converts Excel date serial numbers (what spreadsheet date cells actually
 *   contain) into Y-m-d.
 */
final class IClinicSysMapper
{
    /** Fields every row must carry. A diagnosis (icd10_code or disease) is checked separately. */
    public const REQUIRED = ['consultation_date', 'barangay', 'age', 'sex'];

    /** canonical field => accepted header spellings (already normalized: lowercase a-z0-9 only) */
    private const ALIASES = [
        'consultation_date' => ['date', 'consultationdate', 'dateofconsultation', 'consultdate', 'visitdate'],
        'barangay'          => ['sitiobarangay', 'barangay', 'brgy', 'sitio', 'barangaysitio'],
        'age'               => ['age'],
        'sex'               => ['sex', 'gender'],
        'icd10_code'        => ['icd10code', 'icd10', 'icd10cm', 'icdcode', 'icd'],
        'diagnosis'         => ['diagnosis'],
        'disease'           => ['disease', 'diseasename'],
    ];

    public static function normalizeHeader(mixed $header): string
    {
        return strtolower(preg_replace('/[^a-z0-9]+/i', '', (string) $header));
    }

    /**
     * @param  array<int, mixed>  $headers  header cells of the header row, in column order
     * @return array{fields: array<int,string>, discarded: string[], missing: string[], found: string[]}
     */
    public static function mapHeaders(array $headers): array
    {
        $lookup = [];
        foreach (self::ALIASES as $field => $aliases) {
            foreach ($aliases as $alias) {
                $lookup[$alias] = $field;
            }
        }

        $fields = [];
        $assigned = [];
        $discarded = [];
        $found = [];

        foreach ($headers as $col => $raw) {
            $label = trim((string) $raw);
            if ($label === '') {
                continue;
            }
            $found[] = $label;

            $field = $lookup[self::normalizeHeader($label)] ?? null;

            if ($field !== null && !isset($assigned[$field])) {
                $fields[$col] = $field;
                $assigned[$field] = true;
            } else {
                $discarded[] = $label;
            }
        }

        $missing = array_values(array_filter(self::REQUIRED, fn ($f) => !isset($assigned[$f])));
        if (!isset($assigned['icd10_code']) && !isset($assigned['disease'])) {
            $missing[] = 'icd10_code';
        }

        return ['fields' => $fields, 'discarded' => $discarded, 'missing' => $missing, 'found' => $found];
    }

    /**
     * Returns Y-m-d, or null if the value is not a recognizable date.
     * Accepts DateTime objects, Excel serial numbers (e.g. 46267), and
     * date strings (2026-09-02, 09/02/2026).
     */
    public static function parseDate(mixed $value): ?string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        if (is_string($value)) {
            $value = trim($value);
        }

        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            $serial = (float) $value;
            // Excel serial range ~1954..2118. Anything else numeric is not a date.
            if ($serial >= 20000 && $serial <= 80000) {
                return (new \DateTimeImmutable('1899-12-30 00:00:00'))
                    ->modify('+' . (int) floor($serial) . ' days')
                    ->format('Y-m-d');
            }

            return null;
        }

        $parsed = date_create((string) $value);

        return $parsed ? $parsed->format('Y-m-d') : null;
    }
}
