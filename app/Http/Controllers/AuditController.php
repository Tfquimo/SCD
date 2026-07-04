<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AuditController extends Controller
{
    /**
     * Display audit logs with filters (Admin only).
     */
    public function index(Request $request)
    {
        Gate::authorize('view-audit-logs');

        $query = AuditLog::with('user')->latest('created_at');

        // Filter: by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        // Filter: by action
        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
        }

        // Filter: date range
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->input('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->input('to'));
        }

        $logs    = $query->paginate(25)->withQueryString();
        $users   = User::orderBy('name')->get(['id', 'name', 'email']);
        $actions = AuditLog::select('action')->distinct()->orderBy('action')->pluck('action');

        return view('audit.index', compact('logs', 'users', 'actions'));
    }
}
