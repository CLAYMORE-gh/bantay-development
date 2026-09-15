<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Disease;
use App\Models\SurveillanceRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SurveillanceRuleController extends Controller
{
    public function index()
    {
        $rules = SurveillanceRule::with('disease')->orderBy('disease_id')->orderBy('priority')->paginate(20);
        return view('admin.rules.index', compact('rules'));
    }

    public function create()
    {
        $diseases = Disease::active()->orderBy('name')->get();
        return view('admin.rules.create', compact('diseases'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'disease_id' => 'required|exists:diseases,id',
            'threshold_type' => 'required|in:absolute,percentage_increase,consecutive_increase,geographic_spread',
            'threshold_value' => 'required|numeric|min:0',
            'severity' => 'required|in:watch,alert,critical',
            'priority' => 'required|integer|min:1|max:255',
            'effective_date' => 'required|date',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['created_by'] = Auth::id();

        SurveillanceRule::create($validated);

        return redirect()->route('admin.rules.index')->with('status', 'Rule created.');
    }

    public function edit(SurveillanceRule $rule)
    {
        $diseases = Disease::active()->orderBy('name')->get();
        return view('admin.rules.edit', compact('rule', 'diseases'));
    }

    public function update(Request $request, SurveillanceRule $rule)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'disease_id' => 'required|exists:diseases,id',
            'threshold_type' => 'required|in:absolute,percentage_increase,consecutive_increase,geographic_spread',
            'threshold_value' => 'required|numeric|min:0',
            'severity' => 'required|in:watch,alert,critical',
            'priority' => 'required|integer|min:1|max:255',
            'effective_date' => 'required|date',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $rule->update($validated);

        return redirect()->route('admin.rules.index')->with('status', 'Rule updated.');
    }

    // Rules are versioned, not deleted — deactivating preserves history for
    // any alerts that were generated while it was active.
    public function destroy(SurveillanceRule $rule)
    {
        $rule->update(['is_active' => false]);
        return redirect()->route('admin.rules.index')->with('status', 'Rule deactivated.');
    }
}
