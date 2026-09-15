<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Disease;
use App\Models\ReportingPeriod;
use App\Services\DashboardAnalyticsService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request, DashboardAnalyticsService $analytics)
    {
        $periodId = $request->integer('period') ?: null;
        $groupBy = $request->get('group_by', 'week');

        $summary = $analytics->summary();
        $diseaseCounts = $analytics->diseaseCounts($periodId);
        $ageBrackets = $analytics->ageBracketSummary(null, $periodId);
        $trend = $analytics->trend($groupBy);

        $periods = ReportingPeriod::active()->orderByDesc('start_date')->get();

        return view('admin.dashboard.index', compact(
            'summary', 'diseaseCounts', 'ageBrackets', 'trend', 'periods', 'periodId', 'groupBy'
        ));
    }

    public function geographic(Request $request, DashboardAnalyticsService $analytics)
    {
        $periodId = $request->integer('period') ?: null;

        $barangayCounts = $analytics->barangayCaseCounts($periodId);
        $periods = ReportingPeriod::active()->orderByDesc('start_date')->get();

        return view('admin.dashboard.geographic', compact('barangayCounts', 'periods', 'periodId'));
    }
}
