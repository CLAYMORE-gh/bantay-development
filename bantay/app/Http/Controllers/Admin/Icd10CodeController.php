<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Disease;
use App\Models\Icd10Code;
use Illuminate\Http\Request;

class Icd10CodeController extends Controller
{
    public function index()
    {
        $codes = Icd10Code::with('disease')->orderBy('code')->paginate(15);
        return view('admin.icd10-codes.index', compact('codes'));
    }

    public function create()
    {
        $diseases = Disease::active()->orderBy('name')->get();
        return view('admin.icd10-codes.create', compact('diseases'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:icd10_codes,code',
            'description' => 'required|string|max:255',
            'disease_id' => 'nullable|exists:diseases,id',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        Icd10Code::create($validated);

        return redirect()->route('admin.icd10-codes.index')
            ->with('status', 'ICD-10 code created.');
    }

    public function edit(Icd10Code $icd10Code)
    {
        $diseases = Disease::active()->orderBy('name')->get();
        return view('admin.icd10-codes.edit', compact('icd10Code', 'diseases'));
    }

    public function update(Request $request, Icd10Code $icd10Code)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:icd10_codes,code,' . $icd10Code->id,
            'description' => 'required|string|max:255',
            'disease_id' => 'nullable|exists:diseases,id',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $icd10Code->update($validated);

        return redirect()->route('admin.icd10-codes.index')
            ->with('status', 'ICD-10 code updated.');
    }

    public function destroy(Icd10Code $icd10Code)
    {
        $icd10Code->update(['is_active' => false]);

        return redirect()->route('admin.icd10-codes.index')
            ->with('status', 'ICD-10 code deactivated.');
    }
}
