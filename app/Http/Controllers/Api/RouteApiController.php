<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JeepneyRoute;
use App\Services\RouteFinderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class RouteApiController extends Controller
{
    /**
     * Get all available routes.
     * Supports If-Modified-Since header for cache validation.
     */
    public function index(Request $request): JsonResponse
    {
        // Check If-Modified-Since for 304
        $lastModified = JeepneyRoute::where('status', 'available')->max('updated_at');
        if ($lastModified && $this->notModifiedSince($request, $lastModified)) {
            return response()->json(null, 304);
        }

        $routes = JeepneyRoute::where('status', 'available')
            ->select('id', 'route_number', 'name', 'path', 'waypoints',
                    'start_point', 'end_point', 'total_distance', 'color',
                    'estimated_time', 'fare', 'updated_at')
            ->orderBy('route_number')
            ->get();

        return $this->withLastModified(response()->json([
            'success' => true,
            'count' => $routes->count(),
            'data' => $routes,
        ]), $lastModified);
    }

    /**
     * Get specific route details
     */
    public function show($id): JsonResponse
    {
        $route = JeepneyRoute::find($id);

        if (!$route) {
            return response()->json([
                'success' => false,
                'message' => 'Route not found',
            ], 404);
        }

        return $this->withLastModified(response()->json([
            'success' => true,
            'data' => $route,
        ]), $route->updated_at);
    }

    /**
     * Find routes between two points — supports 0, 1, and 2 transfers.
     *
     * Delegates all computation to RouteFinderService.
     */
    public function findRoutes(Request $request): JsonResponse
    {
        $request->validate([
            'from_lat'              => 'required|numeric',
            'from_lng'              => 'required|numeric',
            'to_lat'                => 'required|numeric',
            'to_lng'                => 'required|numeric',
            'tolerance'             => 'nullable|numeric|min:0.1|max:2',
            'transfer_walk_max'     => 'nullable|numeric|min:0.1|max:1',
            'include_walking_paths' => 'nullable|boolean',
        ]);

        $service = app(RouteFinderService::class);

        $results = $service->findRoutes(
            fromLat:             (float) $request->from_lat,
            fromLng:             (float) $request->from_lng,
            toLat:               (float) $request->to_lat,
            toLng:               (float) $request->to_lng,
            tolerance:           (float) ($request->tolerance ?? 1.0),
            transferWalkMax:     (float) ($request->transfer_walk_max ?? 0.3),
            includeWalkingPaths: $request->boolean('include_walking_paths', false),
        );

        return response()->json([
            'success'     => true,
            'origin'      => ['lat' => (float) $request->from_lat, 'lng' => (float) $request->from_lng],
            'destination' => ['lat' => (float) $request->to_lat,   'lng' => (float) $request->to_lng],
            'count'       => count($results),
            'data'        => $results,
        ]);
    }

    /**
     * Get all route paths for map display (lightweight).
     *
     * Supports:
     *  - ?since=ISO8601  → only return routes updated after this timestamp
     *  - If-Modified-Since header → return 304 if nothing changed
     */
    public function getAllPaths(Request $request): JsonResponse
    {
        $lastModified = JeepneyRoute::where('status', 'available')->max('updated_at');

        // Check If-Modified-Since for 304
        if ($lastModified && $this->notModifiedSince($request, $lastModified)) {
            return response()->json(null, 304);
        }

        $query = JeepneyRoute::where('status', 'available')
            ->select('id', 'route_number', 'name', 'path', 'color', 'updated_at');

        // Delta sync: only routes changed since a given timestamp
        if ($request->filled('since')) {
            try {
                $since = Carbon::parse($request->since);
                $query->where('updated_at', '>', $since);
            } catch (\Exception $e) {
                // Ignore invalid date, return all
            }
        }

        $routes = $query->get();

        return $this->withLastModified(response()->json([
            'success'       => true,
            'count'         => $routes->count(),
            'last_modified' => $lastModified,
            'data'          => $routes,
        ]), $lastModified);
    }

    // ─── Helpers ─────────────────────────────────────────────────────

    /**
     * Check if the client's If-Modified-Since header indicates no changes.
     */
    private function notModifiedSince(Request $request, $lastModified): bool
    {
        $ifModifiedSince = $request->header('If-Modified-Since');
        if (!$ifModifiedSince || !$lastModified) return false;

        try {
            $clientDate = Carbon::parse($ifModifiedSince);
            $serverDate = Carbon::parse($lastModified);
            return $serverDate->lte($clientDate);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Attach Last-Modified header to a response.
     */
    private function withLastModified(JsonResponse $response, $lastModified): JsonResponse
    {
        if ($lastModified) {
            $response->header('Last-Modified', Carbon::parse($lastModified)->toRfc7231String());
        }
        return $response;
    }
}
