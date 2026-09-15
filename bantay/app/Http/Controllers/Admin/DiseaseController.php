<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Disease;
use Illuminate\Http\Request;

class DiseaseController extends Controller
{
    public function index()
    {
        $diseases = Disease::orderBy('name')->paginate(15);
        return view('admin.diseases.index', compact('diseases'));
    }

    public function create()
    {
        return view('admin.diseases.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:diseases,name',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        Disease::create($validated);

        return redirect()->route('admin.diseases.index')
            ->with('status', 'Disease created.');
    }

    public function edit(Disease $disease)
    {
        return view('admin.diseases.edit', compact('disease'));
    }

    public function update(Request $request, Disease $disease)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:diseases,name,' . $disease->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $disease->update($validated);

        return redirect()->route('admin.diseases.index')
            ->with('status', 'Disease updated.');
    }

    public function destroy(Disease $disease)
    {
        $disease->update(['is_active' => false]);

        return redirect()->route('admin.diseases.index')
            ->with('status', 'Disease deactivated.');
    }
}
