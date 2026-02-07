<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JeepneyRoute;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class RouteController extends Controller
{
    /**
     * Display a listing of routes
     */
    public function index(Request $request)
    {
        $query = JeepneyRoute::query();

        // Search
        if ($request->filled('search')) {
            $search = str_replace(['%', '_'], ['\%', '\_'], trim($request->search));
            $query->where(function ($q) use ($search) {
                $q->where('route_number', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('start_point', 'like', "%{$search}%")
                  ->orWhere('end_point', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Combined filter (status + sort)
        $filter = $request->get('filter', 'all');

        switch ($filter) {
            case 'available':
                $query->where('status', 'available');
                break;
            case 'unavailable':
                $query->where('status', 'unavailable');
                break;
        }

        // Sorting
        switch ($filter) {
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            default:
                $query->orderBy('name', 'asc');
                break;
        }

        $routes = $query->paginate(10);

        // Get statistics
        $stats = [
            'total' => JeepneyRoute::count(),
            'available' => JeepneyRoute::where('status', 'available')->count(),
            'unavailable' => JeepneyRoute::where('status', 'unavailable')->count(),
            'total_distance' => JeepneyRoute::sum('total_distance'),
        ];

        return view('admin.routes.index', compact('routes', 'stats'));
    }

    /**
     * Show the form for creating a new route
     */
    public function create()
    {
        return view('admin.routes.create');
    }

    /**
     * Store a newly created route
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'terminal' => 'required|string|max:255',
            'path' => 'required|string',
            'waypoints' => 'nullable|string',
            'status' => 'required|in:available,unavailable',
            'color' => 'nullable|string|max:7',
            'description' => 'nullable|string'
        ]);

        // Decode JSON path
        $pathArray = json_decode($validated['path'], true);
        
        if (!$pathArray || count($pathArray) < 2) {
            return back()->withInput()->withErrors(['path' => 'Please draw at least 2 waypoints on the map.']);
        }

        $validated['path'] = $pathArray;

        // Decode waypoints if provided
        if (!empty($validated['waypoints'])) {
            $validated['waypoints'] = json_decode($validated['waypoints'], true);
        }

        // Set default color if not provided
        if (empty($validated['color'])) {
            $validated['color'] = '#EBAF3E';
        }

        // Set default status if not provided
        if (empty($validated['status'])) {
            $validated['status'] = 'available';
        }

        $route = JeepneyRoute::create($validated);

        // Calculate and update distance
        $route->total_distance = $route->calculateDistance();
        $route->save();

        // Log activity
        ActivityLog::log(
            'created',
            'Route',
            $route->id,
            $route->name,
            "New route '{$route->name}' was created"
        );

        return redirect()->route('admin.routes.index')
            ->with('success', 'Route "' . $route->name . '" created successfully!');
    }

    /**
     * Show the form for editing a route
     */
    public function edit(JeepneyRoute $route)
    {
        return view('admin.routes.edit', compact('route'));
    }

    /**
     * Update the specified route
     */
    public function update(Request $request, JeepneyRoute $route)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'terminal' => 'required|string|max:255',
            'path' => 'required|string',
            'waypoints' => 'nullable|string',
            'status' => 'required|in:available,unavailable',
            'color' => 'nullable|string|max:7',
            'description' => 'nullable|string'
        ]);

        // Decode JSON path
        $pathArray = json_decode($validated['path'], true);
        
        if (!$pathArray || count($pathArray) < 2) {
            return back()->withInput()->withErrors(['path' => 'Please draw at least 2 waypoints on the map.']);
        }

        $validated['path'] = $pathArray;

        // Decode waypoints if provided
        if (!empty($validated['waypoints'])) {
            $validated['waypoints'] = json_decode($validated['waypoints'], true);
        } else {
            $validated['waypoints'] = null;
        }

        $route->update($validated);

        // Recalculate distance
        $route->total_distance = $route->calculateDistance();
        $route->save();

        // Log activity
        ActivityLog::log(
            'updated',
            'Route',
            $route->id,
            $route->name,
            "Route '{$route->name}' was updated"
        );

        return redirect()->route('admin.routes.index')
            ->with('success', 'Route "' . $route->name . '" updated successfully!');
    }

    /**
     * Remove the specified route
     */
    public function destroy(JeepneyRoute $route)
    {
        $routeName = $route->name;
        $name = $route->name;
        $route->delete();

        // Log activity
        ActivityLog::log(
            'deleted',
            'Route',
            null,
            $name,
            "Route '{$name}' was deleted"
        );

        return redirect()->route('admin.routes.index')
            ->with('success', 'Route "' . $routeName . '" deleted successfully!');
    }

    /**
     * Toggle route status
     */
    public function toggleStatus(JeepneyRoute $route)
    {
        $route->status = $route->status === 'available' ? 'unavailable' : 'available';
        $route->save();

        $statusText = $route->status === 'available' ? 'activated' : 'deactivated';

        return back()->with('success', 'Route "' . $route->name . '" ' . $statusText . '!');
    }

    /**
     * Show route details (for AJAX modal)
     */
    public function show(JeepneyRoute $route)
    {
        return response()->json([
            'success' => true,
            'data' => $route
        ]);
    }

    /**
     * Batch delete routes
     */
    public function batchDelete(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|exists:jeepney_routes,id'
        ]);

        $ids = $validated['ids'];
        $routes = JeepneyRoute::whereIn('id', $ids)->get();
        
        if ($routes->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No routes found to delete'
            ], 404);
        }

        $deletedCount = 0;
        $deletedNames = [];

        foreach ($routes as $route) {
            $deletedNames[] = $route->name;

            // Delete the route
            $route->delete();
            $deletedCount++;

            // Log activity
            ActivityLog::log(
                'deleted',
                'Route',
                null,
                $route->name,
                "Route '{$route->name}' was batch deleted"
            );
        }

        return response()->json([
            'success' => true,
            'message' => "Successfully deleted {$deletedCount} route(s): " . implode(', ', $deletedNames),
            'deleted_count' => $deletedCount
        ]);
    }
}
