<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        abort_if(! auth()->user()->hasPermissionTo('panel.admin'), 403);

        $query = ActivityLog::latest('created_at');

        if ($module = $request->input('module')) {
            $query->where('event', 'like', $module . '.%');
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('causer_name', 'like', "%{$search}%")
                  ->orWhere('event', 'like', "%{$search}%");
            });
        }

        if ($from = $request->input('date_from')) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to = $request->input('date_to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $logs = $query->paginate(50)->withQueryString();

        // Distinct module names for the filter dropdown
        $modules = ActivityLog::pluck('event')
            ->map(fn ($event) => explode('.', $event)[0])
            ->unique()
            ->sort()
            ->values();

        $totalLogs = ActivityLog::count();

        return view('admin.activity-log.index', compact('logs', 'modules', 'totalLogs'));
    }

    public function clear(): RedirectResponse
    {
        abort_if(! auth()->user()->hasPermissionTo('panel.admin'), 403);

        ActivityLog::truncate();

        // Log the clear action itself as the first new entry
        ActivityLogger::log('activity_log.cleared', 'Activity log was cleared');

        return redirect()->route('admin.activity-log.index')
            ->with('success', 'Activity log cleared successfully.');
    }
}
