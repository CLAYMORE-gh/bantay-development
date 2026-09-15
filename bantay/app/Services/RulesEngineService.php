<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\AlertHistory;
use App\Models\ReportingPeriod;
use App\Models\SurveillanceRecord;
use App\Models\SurveillanceRule;
use Illuminate\Support\Facades\DB;

class RulesEngineService
{
    /**
     * Evaluate every active rule against one reporting period's data.
     * Returns the alerts that were created or updated (triggered_value changed).
     */
    public function evaluatePeriod(ReportingPeriod $period): array
    {
        $results = [];

        $rules = SurveillanceRule::active()->get();

        foreach ($rules as $rule) {
            $matches = match ($rule->threshold_type) {
                'absolute' => $this->evaluateAbsolute($rule, $period),
                'percentage_increase' => $this->evaluatePercentageIncrease($rule, $period),
                'consecutive_increase' => $this->evaluateConsecutiveIncrease($rule, $period),
                'geographic_spread' => $this->evaluateGeographicSpread($rule, $period),
                default => [],
            };

            foreach ($matches as $match) {
                $results[] = $this->upsertAlert($rule, $period, $match);
            }
        }

        return array_filter($results);
    }

    /**
     * Absolute threshold: total valid case count for this disease in this
     * period, per barangay, compared against the threshold.
     */
    private function evaluateAbsolute(SurveillanceRule $rule, ReportingPeriod $period): array
    {
        $counts = $this->countsByBarangay($rule->disease_id, $period->id);

        return collect($counts)
            ->filter(fn ($count) => $count >= $rule->threshold_value)
            ->map(fn ($count, $barangayId) => ['barangay_id' => $barangayId ?: null, 'value' => $count])
            ->values()
            ->all();
    }

    /**
     * Percentage increase vs the immediately preceding period (by start_date),
     * city-wide (not per barangay).
     */
    private function evaluatePercentageIncrease(SurveillanceRule $rule, ReportingPeriod $period): array
    {
        $previous = ReportingPeriod::where('start_date', '<', $period->start_date)
            ->orderByDesc('start_date')
            ->first();

        if (!$previous) {
            return [];
        }

        $currentCount = $this->totalCount($rule->disease_id, $period->id);
        $previousCount = $this->totalCount($rule->disease_id, $previous->id);

        if ($previousCount === 0) {
            return []; // avoid division by zero / meaningless infinite increase
        }

        $percentIncrease = (($currentCount - $previousCount) / $previousCount) * 100;

        if ($percentIncrease >= $rule->threshold_value) {
            return [['barangay_id' => null, 'value' => round($percentIncrease, 2)]];
        }

        return [];
    }

    /**
     * Consecutive rising periods: count case totals for the last N+1 periods
     * (N = threshold_value) and check each is strictly greater than the last.
     */
    private function evaluateConsecutiveIncrease(SurveillanceRule $rule, ReportingPeriod $period): array
    {
        $n = (int) $rule->threshold_value;

        $periods = ReportingPeriod::where('start_date', '<=', $period->start_date)
            ->orderByDesc('start_date')
            ->limit($n + 1)
            ->get()
            ->reverse()
            ->values();

        if ($periods->count() < $n + 1) {
            return []; // not enough history yet
        }

        $counts = $periods->map(fn ($p) => $this->totalCount($rule->disease_id, $p->id));

        $rising = true;
        for ($i = 1; $i < $counts->count(); $i++) {
            if ($counts[$i] <= $counts[$i - 1]) {
                $rising = false;
                break;
            }
        }

        if ($rising) {
            return [['barangay_id' => null, 'value' => $counts->last()]];
        }

        return [];
    }

    /**
     * Geographic spread: number of distinct barangays with at least one case
     * of this disease in this period.
     */
    private function evaluateGeographicSpread(SurveillanceRule $rule, ReportingPeriod $period): array
    {
        $distinctBarangays = SurveillanceRecord::query()
            ->whereIn('validation_status', ['valid', 'corrected'])
            ->where('disease_id', $rule->disease_id)
            ->where('reporting_period_id', $period->id)
            ->whereNotNull('barangay_id')
            ->distinct('barangay_id')
            ->count('barangay_id');

        if ($distinctBarangays >= $rule->threshold_value) {
            return [['barangay_id' => null, 'value' => $distinctBarangays]];
        }

        return [];
    }

    private function countsByBarangay(int $diseaseId, int $periodId): array
    {
        return SurveillanceRecord::query()
            ->whereIn('validation_status', ['valid', 'corrected'])
            ->where('disease_id', $diseaseId)
            ->where('reporting_period_id', $periodId)
            ->select('barangay_id', DB::raw('count(*) as total'))
            ->groupBy('barangay_id')
            ->pluck('total', 'barangay_id')
            ->all();
    }

    private function totalCount(int $diseaseId, int $periodId): int
    {
        return SurveillanceRecord::query()
            ->whereIn('validation_status', ['valid', 'corrected'])
            ->where('disease_id', $diseaseId)
            ->where('reporting_period_id', $periodId)
            ->count();
    }

    /**
     * Create a new alert, or update an existing open one for the same
     * rule+period+barangay combo (avoids duplicate alerts on re-evaluation).
     * Conflict handling (FR-20): if an alert already exists for this
     * disease+period+barangay from a DIFFERENT rule, only replace it if the
     * new rule's severity is higher — the highest-severity result wins.
     */
    private function upsertAlert(SurveillanceRule $rule, ReportingPeriod $period, array $match): ?Alert
    {
        $existingSameRule = Alert::where('surveillance_rule_id', $rule->id)
            ->where('reporting_period_id', $period->id)
            ->where('barangay_id', $match['barangay_id'])
            ->first();

        if ($existingSameRule) {
            if ($existingSameRule->triggered_value != $match['value']) {
                $existingSameRule->update(['triggered_value' => $match['value']]);
            }
            return $existingSameRule;
        }

        // Check for a competing alert from a different rule, same disease+period+barangay.
        $competing = Alert::where('disease_id', $rule->disease_id)
            ->where('reporting_period_id', $period->id)
            ->where('barangay_id', $match['barangay_id'])
            ->openStatus()
            ->first();

        if ($competing && $competing->severityWeight() >= $this->severityWeight($rule->severity)) {
            // Existing alert is equal or higher severity — don't create a lesser duplicate.
            return null;
        }

        $alert = Alert::create([
            'surveillance_rule_id' => $rule->id,
            'disease_id' => $rule->disease_id,
            'barangay_id' => $match['barangay_id'],
            'reporting_period_id' => $period->id,
            'severity' => $rule->severity,
            'triggered_value' => $match['value'],
            'threshold_value' => $rule->threshold_value,
            'status' => 'open',
        ]);

        AlertHistory::create([
            'alert_id' => $alert->id,
            'from_status' => null,
            'to_status' => 'open',
            'notes' => "Auto-generated by rule '{$rule->name}'.",
        ]);

        return $alert;
    }

    private function severityWeight(string $severity): int
    {
        return match ($severity) {
            'critical' => 3, 'alert' => 2, 'watch' => 1, default => 0,
        };
    }
}
