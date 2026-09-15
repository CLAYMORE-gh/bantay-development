<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReportingPeriod;
use Illuminate\Http\Request;

class ReportingPeriodController extends Controller
{
    public function index()
    {
        $periods = ReportingPeriod::orderByDesc('start_date')->paginate(15);
        return view('admin.reporting-periods.index', compact('periods'));
    }

    public function create()
    {
        return view('admin.reporting-periods.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|in:open,closed',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        ReportingPeriod::create($validated);

        return redirect()->route('admin.reporting-periods.index')
            ->with('status', 'Reporting period created.');
    }

    public function edit(ReportingPeriod $reportingPeriod)
    {
        return view('admin.reporting-periods.edit', compact('reportingPeriod'));
    }

    public function update(Request $request, ReportingPeriod $reportingPeriod)
    {
        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|in:open,closed',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $reportingPeriod->update($validated);

        return redirect()->route('admin.reporting-periods.index')
            ->with('status', 'Reporting period updated.');
    }

    public function destroy(ReportingPeriod $reportingPeriod)
    {
        $reportingPeriod->update(['is_active' => false]);

        return redirect()->route('admin.reporting-periods.index')
            ->with('status', 'Reporting period deactivated.');
    }
}
