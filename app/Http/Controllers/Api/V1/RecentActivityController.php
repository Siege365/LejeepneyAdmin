<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\RecentActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RecentActivityController extends Controller
{
    /**
     * Get user's recent activities (paginated)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Return empty for guests
        if (!$user) {
            return response()->json([
                'success' => true,
                'data' => [],
                'meta' => [
                    'total' => 0,
                    'per_page' => 20,
                    'current_page' => 1
                ]
            ]);
        }

        $limit = min($request->input('limit', 20), 50); // Max 50 per page
        
        $query = RecentActivity::forUser($user->id)->recent();
        
        // Filter by activity type if provided
        if ($request->filled('activity_type')) {
            $query->ofType($request->activity_type);
        }
        
        $activities = $query->paginate($limit);
        
        return response()->json([
            'success' => true,
            'data' => $activities->items(),
            'meta' => [
                'total' => $activities->total(),
                'per_page' => $activities->perPage(),
                'current_page' => $activities->currentPage(),
                'last_page' => $activities->lastPage()
            ]
        ]);
    }

    /**
     * Create a new recent activity
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'activity_type' => 'required|in:route_calculated,fare_calculated,location_search,route_saved,ticket_created,ticket_replied,ticket_status_changed',
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string',
            'from_location' => 'nullable|string|max:255',
            'to_location' => 'nullable|string|max:255',
            'route_names' => 'nullable|string',
            'fare' => 'nullable|numeric|min:0|max:9999.99',
            'metadata' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        
        // Limit to 50 most recent activities per user
        if ($user) {
            $userActivitiesCount = RecentActivity::forUser($user->id)->count();
            if ($userActivitiesCount >= 50) {
                // Delete oldest activity
                RecentActivity::forUser($user->id)
                    ->orderBy('created_at', 'asc')
                    ->first()
                    ->delete();
            }
        }

        $activity = RecentActivity::create([
            'user_id' => $user?->id,
            'activity_type' => $request->activity_type,
            'title' => $request->title,
            'subtitle' => $request->subtitle,
            'from_location' => $request->from_location,
            'to_location' => $request->to_location,
            'route_names' => $request->route_names,
            'fare' => $request->fare,
            'metadata' => $request->metadata
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Activity created successfully',
            'data' => $activity
        ], 201);
    }

    /**
     * Create multiple activities at once (batch insert)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function batch(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'activities' => 'required|array|max:50',
            'activities.*.activity_type' => 'required|in:route_calculated,fare_calculated,location_search,route_saved,ticket_created,ticket_replied,ticket_status_changed',
            'activities.*.title' => 'required|string|max:255',
            'activities.*.subtitle' => 'nullable|string',
            'activities.*.from_location' => 'nullable|string|max:255',
            'activities.*.to_location' => 'nullable|string|max:255',
            'activities.*.route_names' => 'nullable|string',
            'activities.*.fare' => 'nullable|numeric|min:0|max:9999.99',
            'activities.*.metadata' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $createdActivities = [];

        foreach ($request->activities as $activityData) {
            $createdActivities[] = RecentActivity::create([
                'user_id' => $user?->id,
                'activity_type' => $activityData['activity_type'],
                'title' => $activityData['title'],
                'subtitle' => $activityData['subtitle'] ?? null,
                'from_location' => $activityData['from_location'] ?? null,
                'to_location' => $activityData['to_location'] ?? null,
                'route_names' => $activityData['route_names'] ?? null,
                'fare' => $activityData['fare'] ?? null,
                'metadata' => $activityData['metadata'] ?? null
            ]);
        }

        // Enforce 50 activity limit for user
        if ($user) {
            $totalCount = RecentActivity::forUser($user->id)->count();
            if ($totalCount > 50) {
                $deleteCount = $totalCount - 50;
                RecentActivity::forUser($user->id)
                    ->orderBy('created_at', 'asc')
                    ->limit($deleteCount)
                    ->delete();
            }
        }

        return response()->json([
            'success' => true,
            'message' => count($createdActivities) . ' activities created successfully',
            'data' => $createdActivities
        ], 201);
    }

    /**
     * Delete a specific activity
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required'
            ], 401);
        }

        $activity = RecentActivity::forUser($user->id)->find($id);

        if (!$activity) {
            return response()->json([
                'success' => false,
                'message' => 'Activity not found or access denied'
            ], 404);
        }

        $activity->delete();

        return response()->json([
            'success' => true,
            'message' => 'Activity deleted successfully'
        ]);
    }

    /**
     * Clear all user's activities
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function clear(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required'
            ], 401);
        }

        $deletedCount = RecentActivity::forUser($user->id)->delete();

        return response()->json([
            'success' => true,
            'message' => $deletedCount . ' activities cleared successfully'
        ]);
    }
}
