<?php

namespace App\Services;

use App\Models\Barangay;
use App\Models\Disease;
use App\Models\Icd10Code;
use App\Models\ReportingPeriod;
use App\Support\IClinicSysMapper;
use App\Support\NameNormalizer;

class DatasetValidationService
{
    /** @var array<string, Icd10Code>|null */
    private ?array $icdMap = null;

    /** @var array<string, Disease>|null */
    private ?array $diseaseMap = null;

    /** @var array<string, Barangay>|null */
    private ?array $barangayMap = null;

    /**
     * Validate one canonical row (see IClinicSysMapper for the field names).
     *
     * @param  bool  $allowSkip  true during import: rows with no diagnosis, or
     *                           with a non-disease code (ICD-10 chapter Z:
     *                           medical certificate, general exam...), are
     *                           reported as "skip" instead of being treated as
     *                           surveillance cases or quarantined. false when a
     *                           reviewer corrects a quarantined row by hand.
     *
     * @return array{skip: bool, skip_reason: ?string, findings: array, standardized: array, has_blocking: bool}
     */
    public function validateRow(array $row, ?ReportingPeriod $period = null, bool $allowSkip = false): array
    {
        $findings = [];
        $std = [
            'disease_id' => null,
            'icd10_code_id' => null,
            'barangay_id' => null,
            'consultation_date' => null,
            'age' => null,
            'sex' => null,
        ];

        $code = strtoupper(trim((string) ($row['icd10_code'] ?? '')));
        $diseaseName = trim((string) ($row['disease'] ?? ''));

        if ($allowSkip && ($reason = $this->skipReason($code, $diseaseName))) {
            return ['skip' => true, 'skip_reason' => $reason, 'findings' => [], 'standardized' => $std, 'has_blocking' => false];
        }

        // --- Required fields ---
        $labels = ['consultation_date' => 'Consultation date', 'barangay' => 'Barangay', 'age' => 'Age', 'sex' => 'Sex'];
        foreach ($labels as $field => $label) {
            if (blank($row[$field] ?? null)) {
                $findings[] = $this->blocking($field, "{$label} is required but missing.");
            }
        }
        if ($code === '' && $diseaseName === '') {
            $findings[] = $this->blocking('icd10_code', 'An ICD-10 code is required.');
        }

        // --- Diagnosis: the disease comes from the ICD-10 code ---
        if ($code !== '') {
            $icd = $this->icdMap()[$code] ?? null;

            if (!$icd) {
                $findings[] = $this->blocking('icd10_code', "ICD-10 code '{$code}' is not in the approved reference list.");
            } else {
                $std['icd10_code_id'] = $icd->id;
                $std['disease_id'] = $icd->disease_id;

                if (!$icd->disease_id) {
                    $findings[] = $this->warning('icd10_code', "ICD-10 code '{$code}' is not linked to a disease yet; the record is kept but excluded from disease statistics.");
                } elseif ($diseaseName !== '') {
                    $named = $this->diseaseMap()[NameNormalizer::generic($diseaseName)] ?? null;
                    if ($named && $named->id !== $icd->disease_id) {
                        $findings[] = $this->warning('icd10_code', "ICD-10 code '{$code}' belongs to a different disease than '{$diseaseName}'.");
                    }
                }
            }
        } elseif ($diseaseName !== '') {
            // Older template without an ICD column: match by disease name.
            $named = $this->diseaseMap()[NameNormalizer::generic($diseaseName)] ?? null;
            if (!$named) {
                $findings[] = $this->blocking('disease', "Disease '{$diseaseName}' is not in the approved reference list.");
            } else {
                $std['disease_id'] = $named->id;
                $findings[] = $this->informational('icd10_code', 'No ICD-10 code provided for this row.');
            }
        }

        // --- Barangay (also accepts "SITIO/BARANGAY" values like "IBABA WEST (POB.)") ---
        $barangayRaw = trim((string) ($row['barangay'] ?? ''));
        if ($barangayRaw !== '') {
            $barangay = $this->barangayMap()[NameNormalizer::barangay($barangayRaw)] ?? null;
            if (!$barangay) {
                $findings[] = $this->blocking('barangay', "'{$barangayRaw}' is not in the approved Calapan City barangay list (it may be a sitio or an address outside the city).");
            } else {
                $std['barangay_id'] = $barangay->id;
            }
        }

        // --- Consultation date ---
        $rawDate = $row['consultation_date'] ?? null;
        if (filled($rawDate)) {
            $date = IClinicSysMapper::parseDate($rawDate);
            $shown = is_scalar($rawDate) ? (string) $rawDate : 'the entered value';

            if ($date === null) {
                $findings[] = $this->blocking('consultation_date', "'{$shown}' is not a valid date.");
            } elseif ($date > now()->toDateString()) {
                $findings[] = $this->blocking('consultation_date', 'Consultation date cannot be in the future.');
            } elseif ($period && ($date < $period->start_date->toDateString() || $date > $period->end_date->toDateString())) {
                $findings[] = $this->blocking(
                    'consultation_date',
                    "Consultation date {$date} is outside the selected reporting period ({$period->start_date->format('M j, Y')} - {$period->end_date->format('M j, Y')})."
                );
            } else {
                $std['consultation_date'] = $date;
            }
        }

        // --- Age ---
        $age = $row['age'] ?? null;
        if (filled($age)) {
            if (!is_numeric($age) || (int) $age < 0 || (int) $age > 120) {
                $findings[] = $this->blocking('age', "Age '{$age}' is out of the valid range (0-120).");
            } else {
                $std['age'] = (int) $age;
            }
        }

        // --- Sex ---
        $sex = strtoupper(trim((string) ($row['sex'] ?? '')));
        $sex = ['MALE' => 'M', 'FEMALE' => 'F'][$sex] ?? $sex;
        if ($sex !== '') {
            if (!in_array($sex, ['M', 'F'], true)) {
                $findings[] = $this->blocking('sex', "Sex '{$sex}' must be 'M' or 'F'.");
            } else {
                $std['sex'] = $sex;
            }
        }

        return [
            'skip' => false,
            'skip_reason' => null,
            'findings' => $findings,
            'standardized' => $std,
            'has_blocking' => collect($findings)->contains(fn ($f) => $f['severity'] === 'blocking'),
        ];
    }

    /** Same disease + barangay + date + age + sex = likely duplicate. */
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

    private function skipReason(string $code, string $diseaseName): ?string
    {
        if ($code === '' && $diseaseName === '') {
            return 'no_diagnosis';
        }

        foreach ((array) config('bantay.import.skip_code_prefixes', ['Z']) as $prefix) {
            if ($code !== '' && str_starts_with($code, strtoupper((string) $prefix))) {
                return 'non_disease_code';
            }
        }

        return null;
    }

    private function icdMap(): array
    {
        return $this->icdMap ??= Icd10Code::active()->get()
            ->keyBy(fn ($c) => strtoupper(trim($c->code)))->all();
    }

    private function diseaseMap(): array
    {
        return $this->diseaseMap ??= Disease::active()->get()
            ->keyBy(fn ($d) => NameNormalizer::generic($d->name))->all();
    }

    private function barangayMap(): array
    {
        return $this->barangayMap ??= Barangay::active()->get()
            ->keyBy(fn ($b) => NameNormalizer::barangay($b->name))->all();
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
