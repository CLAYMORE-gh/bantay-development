<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use App\Models\ReportingPeriod;
use App\Services\AlertLifecycleService;
use App\Services\RulesEngineService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AlertController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'open');

        $query = Alert::with('disease', 'barangay', 'reportingPeriod', 'rule')
            ->orderByDesc('created_at');

        if ($status === 'open') {
            $query->openStatus();
        } elseif ($status !== 'all') {
            $query->where('status', $status);
        }

        $alerts = $query->paginate(20)->withQueryString();

        $periods = ReportingPeriod::active()->orderByDesc('start_date')->get();

        return view('admin.alerts.index', compact('alerts', 'status', 'periods'));
    }

    public function show(Alert $alert, AlertLifecycleService $lifecycle)
    {
        $alert->load('disease', 'barangay', 'reportingPeriod', 'rule', 'history.changedBy', 'acknowledgedBy', 'assignedTo', 'resolvedBy');
        $availableTransitions = $lifecycle->availableTransitions($alert);

        return view('admin.alerts.show', compact('alert', 'availableTransitions'));
    }

    public function transition(Request $request, Alert $alert, AlertLifecycleService $lifecycle)
    {
        $validated = $request->validate([
            'to' => 'required|string',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Escalation is a more sensitive action than acknowledging/confirming/
        // dismissing/resolving — requires the separate 'escalate alerts'
        // permission (Health Officer and Administrator only, per the wireframes).
        $requiredPermission = $validated['to'] === 'escalated' ? 'escalate alerts' : 'acknowledge alerts';

        if (!Auth::user()->can($requiredPermission)) {
            abort(403);
        }

        try {
            $lifecycle->transition($alert, $validated['to'], Auth::user(), $validated['notes'] ?? null);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['to' => $e->getMessage()]);
        }

        return redirect()->route('admin.alerts.show', $alert)->with('status', 'Alert updated.');
    }

    /**
     * Manually re-run the rules engine for a reporting period — useful for
     * testing, or for periods uploaded before a rule existed.
     */
    public function evaluate(Request $request, RulesEngineService $engine)
    {
        $validated = $request->validate(['reporting_period_id' => 'required|exists:reporting_periods,id']);
        $period = ReportingPeriod::findOrFail($validated['reporting_period_id']);

        $results = $engine->evaluatePeriod($period);

        return redirect()->route('admin.alerts.index')
            ->with('status', count($results) . ' alert(s) evaluated for ' . $period->label . '.');
    }
}
