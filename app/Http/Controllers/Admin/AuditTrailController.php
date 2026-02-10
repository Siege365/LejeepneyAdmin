<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuditTrailController extends Controller
{
    /**
     * Display the audit trail page
     */
    public function index(Request $request)
    {
        $query = ActivityLog::query();

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by action
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Filter by model type
        if ($request->filled('model_type')) {
            $query->where('model_type', 'like', '%' . $request->model_type . '%');
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('model_name', 'like', "%{$search}%")
                  ->orWhere('user_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        // Get paginated results
        $activities = $query->latest()->paginate(25);

        // Get filter options
        $users = DB::table('activity_logs')
            ->select('user_id', 'user_name')
            ->whereNotNull('user_id')
            ->distinct()
            ->orderBy('user_name')
            ->get();

        $actions = DB::table('activity_logs')
            ->select('action')
            ->distinct()
            ->orderBy('action')
            ->get()
            ->pluck('action');

        $modelTypes = DB::table('activity_logs')
            ->select('model_type')
            ->distinct()
            ->orderBy('model_type')
            ->get()
            ->pluck('model_type');

        return view('admin.audit-trail.index', compact('activities', 'users', 'actions', 'modelTypes'));
    }

    /**
     * View detailed changes for an activity
     */
    public function show($id)
    {
        $activity = ActivityLog::findOrFail($id);
        
        return response()->json([
            'activity' => $activity,
            'changes' => $activity->changes,
            'formatted_changes' => $this->formatChanges($activity->changes)
        ]);
    }

    /**
     * Format changes for display
     */
    private function formatChanges($changes)
    {
        if (!$changes) {
            return null;
        }

        $formatted = [];
        foreach ($changes as $field => $values) {
            $formatted[] = [
                'field' => ucfirst(str_replace('_', ' ', $field)),
                'old' => $values['old'] ?? 'N/A',
                'new' => $values['new'] ?? 'N/A'
            ];
        }

        return $formatted;
    }

    /**
     * Export audit trail to CSV
     */
    public function export(Request $request)
    {
        $query = ActivityLog::query();

        // Apply same filters as index
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('model_type')) {
            $query->where('model_type', 'like', '%' . $request->model_type . '%');
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('model_name', 'like', "%{$search}%")
                  ->orWhere('user_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        $activities = $query->latest()->get();

        $filename = 'audit-trail-' . date('Y-m-d-His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($activities) {
            $file = fopen('php://output', 'w');
            
            // CSV Headers
            fputcsv($file, ['Date & Time', 'User', 'Action', 'Model Type', 'Model Name', 'Description', 'IP Address']);

            // CSV Rows
            foreach ($activities as $activity) {
                fputcsv($file, [
                    $activity->created_at->format('Y-m-d H:i:s'),
                    $activity->user_name,
                    ucfirst(str_replace('_', ' ', $activity->action)),
                    $activity->model_type,
                    $activity->model_name,
                    $activity->description,
                    $activity->ip_address,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
