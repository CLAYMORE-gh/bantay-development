<?php

namespace App\Services;

use App\Models\Barangay;
use App\Models\Disease;
use App\Models\SurveillanceRecord;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardAnalyticsService
{
    /**
     * Case counts grouped by disease. Only counts rows that passed validation
     * (valid or corrected) — quarantined rows are excluded from analysis
     * until they're fixed, per the thesis's data-quality requirements.
     */
    public function diseaseCounts(?int $reportingPeriodId = null): array
    {
        $query = SurveillanceRecord::query()
            ->whereIn('validation_status', ['valid', 'corrected'])
            ->whereNotNull('disease_id')
            ->join('diseases', 'diseases.id', '=', 'surveillance_records.disease_id')
            ->select('diseases.name', DB::raw('count(*) as total'))
            ->groupBy('diseases.name')
            ->orderByDesc('total');

        if ($reportingPeriodId) {
            $query->where('surveillance_records.reporting_period_id', $reportingPeriodId);
        }

        return $query->get()->map(fn ($row) => ['label' => $row->name, 'count' => (int) $row->total])->all();
    }

    /**
     * Case counts by barangay, joined with lat/lng for the map.
     * Barangays with no cases still appear (count 0) so the map shows the
     * full set, not just ones with hits.
     */
    public function barangayCaseCounts(?int $reportingPeriodId = null): array
    {
        $counts = SurveillanceRecord::query()
            ->whereIn('validation_status', ['valid', 'corrected'])
            ->whereNotNull('barangay_id')
            ->when($reportingPeriodId, fn ($q) => $q->where('reporting_period_id', $reportingPeriodId))
            ->select('barangay_id', DB::raw('count(*) as total'))
            ->groupBy('barangay_id')
            ->pluck('total', 'barangay_id');

        return Barangay::active()->get()->map(fn ($b) => [
            'id' => $b->id,
            'name' => $b->name,
            'latitude' => $b->latitude,
            'longitude' => $b->longitude,
            'count' => (int) ($counts[$b->id] ?? 0),
        ])->all();
    }

    /**
     * Age-bracket breakdown, optionally filtered to one disease/period.
     */
    public function ageBracketSummary(?int $diseaseId = null, ?int $reportingPeriodId = null): array
    {
        $brackets = [
            '0-4' => [0, 4], '5-14' => [5, 14], '15-24' => [15, 24],
            '25-44' => [25, 44], '45-64' => [45, 64], '65+' => [65, 120],
        ];

        $query = SurveillanceRecord::query()
            ->whereIn('validation_status', ['valid', 'corrected'])
            ->whereNotNull('age')
            ->when($diseaseId, fn ($q) => $q->where('disease_id', $diseaseId))
            ->when($reportingPeriodId, fn ($q) => $q->where('reporting_period_id', $reportingPeriodId));

        $ages = $query->pluck('age');

        return collect($brackets)->map(function ($range, $label) use ($ages) {
            [$min, $max] = $range;
            return ['label' => $label, 'count' => $ages->filter(fn ($age) => $age >= $min && $age <= $max)->count()];
        })->values()->all();
    }

    /**
     * Case trend over time, grouped by week/month/year.
     */
    public function trend(string $groupBy = 'week', ?int $diseaseId = null): array
    {
        $format = match ($groupBy) {
            'month' => 'YYYY-MM',
            'year' => 'YYYY',
            default => 'IYYY-IW', // ISO year-week, Postgres to_char format
        };

        $rows = SurveillanceRecord::query()
            ->whereIn('validation_status', ['valid', 'corrected'])
            ->whereNotNull('consultation_date')
            ->when($diseaseId, fn ($q) => $q->where('disease_id', $diseaseId))
            ->select(DB::raw("to_char(consultation_date, '{$format}') as bucket"), DB::raw('count(*) as total'))
            ->groupBy('bucket')
            ->orderBy('bucket')
            ->get();

        return $rows->map(fn ($r) => ['label' => $r->bucket, 'count' => (int) $r->total])->all();
    }

    /**
     * Compare total case counts between two reporting periods.
     */
    public function periodComparison(int $periodAId, int $periodBId): array
    {
        $countFor = fn ($periodId) => SurveillanceRecord::query()
            ->whereIn('validation_status', ['valid', 'corrected'])
            ->where('reporting_period_id', $periodId)
            ->count();

        return [
            'period_a' => $countFor($periodAId),
            'period_b' => $countFor($periodBId),
        ];
    }

    /**
     * High-level summary numbers for the top of the dashboard.
     */
    public function summary(): array
    {
        return [
            'total_valid_records' => SurveillanceRecord::whereIn('validation_status', ['valid', 'corrected'])->count(),
            'total_quarantined' => SurveillanceRecord::where('validation_status', 'quarantined')->count(),
            'active_diseases' => Disease::active()->count(),
            'active_barangays' => Barangay::active()->count(),
        ];
    }
}
