<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Barangay;
use Illuminate\Http\Request;

class BarangayController extends Controller
{
    public function index()
    {
        $barangays = Barangay::orderBy('name')->paginate(15);
        return view('admin.barangays.index', compact('barangays'));
    }

    public function create()
    {
        return view('admin.barangays.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:barangays,name',
            'code' => 'nullable|string|max:50|unique:barangays,code',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        Barangay::create($validated);

        return redirect()->route('admin.barangays.index')
            ->with('status', 'Barangay created.');
    }

    public function edit(Barangay $barangay)
    {
        return view('admin.barangays.edit', compact('barangay'));
    }

    public function update(Request $request, Barangay $barangay)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:barangays,name,' . $barangay->id,
            'code' => 'nullable|string|max:50|unique:barangays,code,' . $barangay->id,
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $barangay->update($validated);

        return redirect()->route('admin.barangays.index')
            ->with('status', 'Barangay updated.');
    }

    // Reference data is never hard-deleted (thesis versioning requirement) —
    // "delete" just deactivates the record so history stays intact.
    public function destroy(Barangay $barangay)
    {
        $barangay->update(['is_active' => false]);

        return redirect()->route('admin.barangays.index')
            ->with('status', 'Barangay deactivated.');
    }
}
