<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class AuditTrailController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('user')->orderByDesc('created_at');

        if ($request->filled('action')) {
            $query->where('action', $request->get('action'));
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->get('user_id'));
        }
        if ($request->filled('model')) {
            $query->where('auditable_type', 'like', '%' . $request->get('model'));
        }

        $logs = $query->paginate(30)->withQueryString();
        $users = User::orderBy('name')->get();

        return view('admin.audit-trail.index', compact('logs', 'users'));
    }
}
