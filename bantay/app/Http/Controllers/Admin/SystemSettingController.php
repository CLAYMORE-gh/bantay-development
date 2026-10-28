<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SystemSettingController extends Controller
{
    public function index()
    {
        $settings = SystemSetting::orderBy('id')->get();
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*' => 'nullable|string|max:255',
        ]);

        $before = SystemSetting::pluck('value', 'key');

        foreach ($validated['settings'] as $key => $value) {
            SystemSetting::set($key, $value);
        }

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'settings_updated',
            'before' => $before->toArray(),
            'after' => $validated['settings'],
        ]);

        return redirect()->route('admin.settings.index')->with('status', 'Settings updated.');
    }
}
