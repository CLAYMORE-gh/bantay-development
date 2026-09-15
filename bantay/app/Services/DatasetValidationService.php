<?php

namespace App\Services;

use App\Models\Barangay;
use App\Models\Disease;
use App\Models\Icd10Code;
use Carbon\Carbon;

class DatasetValidationService
{
    /**
     * Validate one raw row (associative array from the Excel import).
     *
     * Expected raw row keys (case-insensitive headers get normalized by the
     * import to snake_case): disease, icd10_code, barangay, consultation_date,
     * age, sex.
     *
     * Returns an array:
     *   [
     *     'findings' => [ ['field' => ..., 'severity' => ..., 'message' => ...], ... ],
     *     'standardized' => [
     *         'disease_id' => ?int, 'icd10_code_id' => ?int, 'barangay_id' => ?int,
     *         'consultation_date' => ?string, 'age' => ?int, 'sex' => ?string,
     *     ],
     *     'has_blocking' => bool,
     * ]
     */
    public function validateRow(array $row, ?int $reportingPeriodStartYear = null): array
    {
        $findings = [];
        $standardized = [
            'disease_id' => null,
            'icd10_code_id' => null,
            'barangay_id' => null,
            'consultation_date' => null,
            'age' => null,
            'sex' => null,
        ];

        // --- Required fields ---
        foreach (['disease', 'barangay', 'consultation_date', 'age', 'sex'] as $required) {
            if (blank($row[$required] ?? null)) {
                $findings[] = $this->blocking($required, "'{$required}' is required but missing.");
            }
        }

        // --- Disease + ICD-10 relationship ---
        $diseaseName = trim((string) ($row['disease'] ?? ''));
        $disease = null;
        if ($diseaseName !== '') {
            $disease = Disease::active()->whereRaw('LOWER(name) = ?', [strtolower($diseaseName)])->first();
            if (!$disease) {
                $findings[] = $this->blocking('disease', "Disease '{$diseaseName}' is not a recognized/active disease.");
            } else {
                $standardized['disease_id'] = $disease->id;
            }
        }

        $icdCode = trim((string) ($row['icd10_code'] ?? ''));
        if ($icdCode !== '') {
            $icd = Icd10Code::active()->whereRaw('UPPER(code) = ?', [strtoupper($icdCode)])->first();
            if (!$icd) {
                $findings[] = $this->warning('icd10_code', "ICD-10 code '{$icdCode}' was not found in the reference list.");
            } else {
                $standardized['icd10_code_id'] = $icd->id;
                if ($disease && $icd->disease_id && $icd->disease_id !== $disease->id) {
                    $findings[] = $this->warning(
                        'icd10_code',
                        "ICD-10 code '{$icdCode}' is normally associated with a different disease than '{$diseaseName}'."
                    );
                }
            }
        } elseif ($disease) {
            // Missing ICD-10 is informational, not blocking — some source data omits it.
            $findings[] = $this->informational('icd10_code', 'No ICD-10 code provided for this row.');
        }

        // --- Barangay ---
        $barangayName = trim((string) ($row['barangay'] ?? ''));
        if ($barangayName !== '') {
            $barangay = Barangay::active()->whereRaw('LOWER(name) = ?', [strtolower($barangayName)])->first();
            if (!$barangay) {
                $findings[] = $this->blocking('barangay', "Barangay '{$barangayName}' is not a recognized/active barangay.");
            } else {
                $standardized['barangay_id'] = $barangay->id;
            }
        }

        // --- Consultation date ---
        $rawDate = $row['consultation_date'] ?? null;
        if (filled($rawDate)) {
            try {
                $date = $rawDate instanceof \DateTimeInterface
                    ? Carbon::instance($rawDate)
                    : Carbon::parse((string) $rawDate);

                if ($date->isFuture()) {
                    $findings[] = $this->blocking('consultation_date', 'Consultation date cannot be in the future.');
                } else {
                    $standardized['consultation_date'] = $date->toDateString();
                }
            } catch (\Throwable $e) {
                $findings[] = $this->blocking('consultation_date', "'{$rawDate}' is not a valid date.");
            }
        }

        // --- Age ---
        $age = $row['age'] ?? null;
        if (filled($age)) {
            if (!is_numeric($age) || (int) $age < 0 || (int) $age > 120) {
                $findings[] = $this->blocking('age', "Age '{$age}' is out of the valid range (0–120).");
            } else {
                $standardized['age'] = (int) $age;
            }
        }

        // --- Sex ---
        $sex = strtoupper(trim((string) ($row['sex'] ?? '')));
        if ($sex !== '') {
            if (!in_array($sex, ['M', 'F'], true)) {
                $findings[] = $this->blocking('sex', "Sex '{$sex}' must be 'M' or 'F'.");
            } else {
                $standardized['sex'] = $sex;
            }
        }

        $hasBlocking = collect($findings)->contains(fn ($f) => $f['severity'] === 'blocking');

        return [
            'findings' => $findings,
            'standardized' => $standardized,
            'has_blocking' => $hasBlocking,
        ];
    }

    /**
     * Build the row-level dedup fingerprint from standardized values.
     * Same disease + barangay + date + age + sex = likely duplicate.
     */
    public function fingerprint(array $standardized): string
    {
        return hash('sha256', implode('|', [
            $standardized['disease_id'] ?? '',
            $standardized['barangay_id'] ?? '',
            $standardized['consultation_date'] ?? '',
            $standardized['age'] ?? '',
            $standardized['sex'] ?? '',
        ]));
    }

    private function blocking(string $field, string $message): array
    {
        return ['field' => $field, 'severity' => 'blocking', 'message' => $message];
    }

    private function warning(string $field, string $message): array
    {
        return ['field' => $field, 'severity' => 'warning', 'message' => $message];
    }

    private function informational(string $field, string $message): array
    {
        return ['field' => $field, 'severity' => 'informational', 'message' => $message];
    }
}
