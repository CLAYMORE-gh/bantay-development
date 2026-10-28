<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\Barangay;
use App\Models\Disease;
use App\Models\ReportingPeriod;
use App\Models\SurveillanceRecord;
use App\Models\ValidationFinding;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * The 7 report types from Table 8 of the thesis, with the params each needs.
     */
    public const TYPES = [
        'weekly_surveillance' => ['label' => 'Weekly Surveillance Report', 'params' => ['period']],
        'monthly_epidemiological' => ['label' => 'Monthly Epidemiological Report', 'params' => ['period']],
        'disease_specific' => ['label' => 'Disease-Specific Report', 'params' => ['disease', 'period?']],
        'barangay_specific' => ['label' => 'Barangay-Specific Report', 'params' => ['barangay', 'period?']],
        'period_specific' => ['label' => 'Period-Specific Report', 'params' => ['period']],
        'alert_summary' => ['label' => 'Alert Summary Report', 'params' => ['period?']],
        'validation_summary' => ['label' => 'Validation Summary Report', 'params' => ['period?']],
    ];

    public function generate(string $type, array $params): array
    {
        return match ($type) {
            'weekly_surveillance' => $this->weeklySurveillance($params['period_id']),
            'monthly_epidemiological' => $this->monthlyEpidemiological($params['period_id']),
            'disease_specific' => $this->diseaseSpecific($params['disease_id'], $params['period_id'] ?? null),
            'barangay_specific' => $this->barangaySpecific($params['barangay_id'], $params['period_id'] ?? null),
            'period_specific' => $this->periodSpecific($params['period_id']),
            'alert_summary' => $this->alertSummary($params['period_id'] ?? null),
            'validation_summary' => $this->validationSummary($params['period_id'] ?? null),
            default => throw new \InvalidArgumentException("Unknown report type: {$type}"),
        };
    }

    private function baseQuery(?int $periodId = null)
    {
        return SurveillanceRecord::query()
            ->whereIn('validation_status', ['valid', 'corrected'])
            ->when($periodId, fn ($q) => $q->where('reporting_period_id', $periodId));
    }

    public function weeklySurveillance(int $periodId): array
    {
        $period = ReportingPeriod::findOrFail($periodId);

        $byDisease = $this->baseQuery($periodId)
            ->join('diseases', 'diseases.id', '=', 'surveillance_records.disease_id')
            ->select('diseases.name', DB::raw('count(*) as total'))
            ->groupBy('diseases.name')->orderByDesc('total')->get();

        $byBarangay = $this->baseQuery($periodId)
            ->join('barangays', 'barangays.id', '=', 'surveillance_records.barangay_id')
            ->select('barangays.name', DB::raw('count(*) as total'))
            ->groupBy('barangays.name')->orderByDesc('total')->get();

        return [
            'title' => 'Weekly Surveillance Report',
            'subtitle' => $period->label,
            'generated_at' => now(),
            'sections' => [
                ['heading' => 'Cases by Disease', 'rows' => $byDisease->map(fn ($r) => [$r->name, $r->total])->all(), 'columns' => ['Disease', 'Cases']],
                ['heading' => 'Cases by Barangay', 'rows' => $byBarangay->map(fn ($r) => [$r->name, $r->total])->all(), 'columns' => ['Barangay', 'Cases']],
            ],
            'total_cases' => $this->baseQuery($periodId)->count(),
        ];
    }

    public function monthlyEpidemiological(int $periodId): array
    {
        $period = ReportingPeriod::findOrFail($periodId);

        $byDisease = $this->baseQuery($periodId)
            ->join('diseases', 'diseases.id', '=', 'surveillance_records.disease_id')
            ->select('diseases.name', DB::raw('count(*) as total'))
            ->groupBy('diseases.name')->orderByDesc('total')->get();

        $brackets = ['0-4' => [0, 4], '5-14' => [5, 14], '15-24' => [15, 24], '25-44' => [25, 44], '45-64' => [45, 64], '65+' => [65, 120]];
        $ages = $this->baseQuery($periodId)->whereNotNull('age')->pluck('age');
        $ageRows = collect($brackets)->map(fn ($range, $label) => [$label, $ages->filter(fn ($a) => $a >= $range[0] && $a <= $range[1])->count()])->values()->all();

        $bySex = $this->baseQuery($periodId)->whereNotNull('sex')
            ->select('sex', DB::raw('count(*) as total'))->groupBy('sex')->get()
            ->map(fn ($r) => [$r->sex === 'M' ? 'Male' : 'Female', $r->total])->all();

        return [
            'title' => 'Monthly Epidemiological Report',
            'subtitle' => $period->label,
            'generated_at' => now(),
            'sections' => [
                ['heading' => 'Cases by Disease', 'rows' => $byDisease->map(fn ($r) => [$r->name, $r->total])->all(), 'columns' => ['Disease', 'Cases']],
                ['heading' => 'Cases by Age Bracket', 'rows' => $ageRows, 'columns' => ['Age Bracket', 'Cases']],
                ['heading' => 'Cases by Sex', 'rows' => $bySex, 'columns' => ['Sex', 'Cases']],
            ],
            'total_cases' => $this->baseQuery($periodId)->count(),
        ];
    }

    public function diseaseSpecific(int $diseaseId, ?int $periodId = null): array
    {
        $disease = Disease::findOrFail($diseaseId);

        $byPeriod = SurveillanceRecord::query()
            ->whereIn('validation_status', ['valid', 'corrected'])
            ->where('disease_id', $diseaseId)
            ->join('reporting_periods', 'reporting_periods.id', '=', 'surveillance_records.reporting_period_id')
            ->select('reporting_periods.label', DB::raw('count(*) as total'))
            ->groupBy('reporting_periods.label', 'reporting_periods.start_date')
            ->orderBy('reporting_periods.start_date')
            ->get();

        $byBarangay = $this->baseQuery($periodId)
            ->where('disease_id', $diseaseId)
            ->join('barangays', 'barangays.id', '=', 'surveillance_records.barangay_id')
            ->select('barangays.name', DB::raw('count(*) as total'))
            ->groupBy('barangays.name')->orderByDesc('total')->get();

        return [
            'title' => 'Disease-Specific Report',
            'subtitle' => $disease->name . ($periodId ? ' — ' . ReportingPeriod::find($periodId)?->label : ' — All periods'),
            'generated_at' => now(),
            'sections' => [
                ['heading' => 'Cases Over Time', 'rows' => $byPeriod->map(fn ($r) => [$r->label, $r->total])->all(), 'columns' => ['Period', 'Cases']],
                ['heading' => 'Cases by Barangay', 'rows' => $byBarangay->map(fn ($r) => [$r->name, $r->total])->all(), 'columns' => ['Barangay', 'Cases']],
            ],
            'total_cases' => $this->baseQuery($periodId)->where('disease_id', $diseaseId)->count(),
        ];
    }

    public function barangaySpecific(int $barangayId, ?int $periodId = null): array
    {
        $barangay = Barangay::findOrFail($barangayId);

        $byDisease = $this->baseQuery($periodId)
            ->where('barangay_id', $barangayId)
            ->join('diseases', 'diseases.id', '=', 'surveillance_records.disease_id')
            ->select('diseases.name', DB::raw('count(*) as total'))
            ->groupBy('diseases.name')->orderByDesc('total')->get();

        return [
            'title' => 'Barangay-Specific Report',
            'subtitle' => $barangay->name . ($periodId ? ' — ' . ReportingPeriod::find($periodId)?->label : ' — All periods'),
            'generated_at' => now(),
            'sections' => [
                ['heading' => 'Cases by Disease', 'rows' => $byDisease->map(fn ($r) => [$r->name, $r->total])->all(), 'columns' => ['Disease', 'Cases']],
            ],
            'total_cases' => $this->baseQuery($periodId)->where('barangay_id', $barangayId)->count(),
        ];
    }

    public function periodSpecific(int $periodId): array
    {
        $period = ReportingPeriod::findOrFail($periodId);

        $byDisease = $this->baseQuery($periodId)
            ->join('diseases', 'diseases.id', '=', 'surveillance_records.disease_id')
            ->select('diseases.name', DB::raw('count(*) as total'))
            ->groupBy('diseases.name')->orderByDesc('total')->get();

        $byBarangay = $this->baseQuery($periodId)
            ->join('barangays', 'barangays.id', '=', 'surveillance_records.barangay_id')
            ->select('barangays.name', DB::raw('count(*) as total'))
            ->groupBy('barangays.name')->orderByDesc('total')->get();

        $validation = $this->validationCounts($periodId);
        $alerts = Alert::where('reporting_period_id', $periodId)->count();

        return [
            'title' => 'Period-Specific Report',
            'subtitle' => $period->label . ' (' . $period->start_date->format('M d, Y') . ' – ' . $period->end_date->format('M d, Y') . ')',
            'generated_at' => now(),
            'sections' => [
                ['heading' => 'Cases by Disease', 'rows' => $byDisease->map(fn ($r) => [$r->name, $r->total])->all(), 'columns' => ['Disease', 'Cases']],
                ['heading' => 'Cases by Barangay', 'rows' => $byBarangay->map(fn ($r) => [$r->name, $r->total])->all(), 'columns' => ['Barangay', 'Cases']],
                ['heading' => 'Data Quality', 'rows' => [
                    ['Valid/Corrected Records', $this->baseQuery($periodId)->count()],
                    ['Quarantined Records', SurveillanceRecord::where('reporting_period_id', $periodId)->where('validation_status', 'quarantined')->count()],
                    ['Blocking Findings', $validation['blocking']],
                    ['Warning Findings', $validation['warning']],
                    ['Alerts Generated', $alerts],
                ], 'columns' => ['Metric', 'Count']],
            ],
            'total_cases' => $this->baseQuery($periodId)->count(),
        ];
    }

    public function alertSummary(?int $periodId = null): array
    {
        $query = Alert::query()->when($periodId, fn ($q) => $q->where('reporting_period_id', $periodId));

        $bySeverity = (clone $query)->select('severity', DB::raw('count(*) as total'))->groupBy('severity')->pluck('total', 'severity');
        $byStatus = (clone $query)->select('status', DB::raw('count(*) as total'))->groupBy('status')->get();
        $byDisease = (clone $query)->join('diseases', 'diseases.id', '=', 'alerts.disease_id')
            ->select('diseases.name', DB::raw('count(*) as total'))->groupBy('diseases.name')->orderByDesc('total')->get();

        return [
            'title' => 'Alert Summary Report',
            'subtitle' => $periodId ? ReportingPeriod::find($periodId)?->label : 'All periods',
            'generated_at' => now(),
            'sections' => [
                ['heading' => 'By Severity', 'rows' => [
                    ['Watch', $bySeverity['watch'] ?? 0],
                    ['Alert', $bySeverity['alert'] ?? 0],
                    ['Critical', $bySeverity['critical'] ?? 0],
                ], 'columns' => ['Severity', 'Count']],
                ['heading' => 'By Status', 'rows' => $byStatus->map(fn ($r) => [str_replace('_', ' ', ucfirst($r->status)), $r->total])->all(), 'columns' => ['Status', 'Count']],
                ['heading' => 'By Disease', 'rows' => $byDisease->map(fn ($r) => [$r->name, $r->total])->all(), 'columns' => ['Disease', 'Count']],
            ],
            'total_cases' => $query->count(),
        ];
    }

    public function validationSummary(?int $periodId = null): array
    {
        $recordQuery = SurveillanceRecord::query()->when($periodId, fn ($q) => $q->where('reporting_period_id', $periodId));

        $byStatus = (clone $recordQuery)->select('validation_status', DB::raw('count(*) as total'))
            ->groupBy('validation_status')->get();

        $findingQuery = ValidationFinding::query()
            ->when($periodId, fn ($q) => $q->whereHas('record', fn ($r) => $r->where('reporting_period_id', $periodId)));

        $bySeverity = (clone $findingQuery)->select('severity', DB::raw('count(*) as total'))->groupBy('severity')->get();
        $byField = (clone $findingQuery)->select('field', DB::raw('count(*) as total'))->groupBy('field')->orderByDesc('total')->get();

        return [
            'title' => 'Validation Summary Report',
            'subtitle' => $periodId ? ReportingPeriod::find($periodId)?->label : 'All periods',
            'generated_at' => now(),
            'sections' => [
                ['heading' => 'Records by Validation Status', 'rows' => $byStatus->map(fn ($r) => [str_replace('_', ' ', ucfirst($r->validation_status)), $r->total])->all(), 'columns' => ['Status', 'Count']],
                ['heading' => 'Findings by Severity', 'rows' => $bySeverity->map(fn ($r) => [ucfirst($r->severity), $r->total])->all(), 'columns' => ['Severity', 'Count']],
                ['heading' => 'Findings by Field', 'rows' => $byField->map(fn ($r) => [$r->field, $r->total])->all(), 'columns' => ['Field', 'Count']],
            ],
            'total_cases' => $recordQuery->count(),
        ];
    }

    private function validationCounts(int $periodId): array
    {
        $findings = ValidationFinding::whereHas('record', fn ($q) => $q->where('reporting_period_id', $periodId))
            ->select('severity', DB::raw('count(*) as total'))->groupBy('severity')->pluck('total', 'severity');

        return [
            'blocking' => $findings['blocking'] ?? 0,
            'warning' => $findings['warning'] ?? 0,
            'informational' => $findings['informational'] ?? 0,
        ];
    }
}
