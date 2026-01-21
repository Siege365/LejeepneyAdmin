<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JeepneyRoute;
use Illuminate\Http\Request;

class RouteApiController extends Controller
{
    /**
     * Get all available routes
     */
    public function index()
    {
        $routes = JeepneyRoute::where('status', 'available')
            ->select('id', 'route_number', 'name', 'path', 'waypoints', 
                    'start_point', 'end_point', 'total_distance', 'color', 
                    'estimated_time', 'fare')
            ->orderBy('route_number')
            ->get();

        return response()->json([
            'success' => true,
            'count' => $routes->count(),
            'data' => $routes
        ]);
    }

    /**
     * Get specific route details
     */
    public function show($id)
    {
        $route = JeepneyRoute::find($id);

        if (!$route) {
            return response()->json([
                'success' => false,
                'message' => 'Route not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $route
        ]);
    }

    /**
     * Find routes between two points
     * Main algorithm for route suggestion
     */
    public function findRoutes(Request $request)
    {
        $request->validate([
            'from_lat' => 'required|numeric',
            'from_lng' => 'required|numeric',
            'to_lat' => 'required|numeric',
            'to_lng' => 'required|numeric',
            'tolerance' => 'nullable|numeric|min:0.1|max:2'
        ]);

        $fromLat = $request->from_lat;
        $fromLng = $request->from_lng;
        $toLat = $request->to_lat;
        $toLng = $request->to_lng;
        $tolerance = $request->tolerance ?? 0.5; // 500m default

        $routes = JeepneyRoute::where('status', 'available')->get();
        $matchingRoutes = [];

        foreach ($routes as $route) {
            // Check if route passes near both origin and destination
            $nearOrigin = $route->isPointNearRoute($fromLat, $fromLng, $tolerance);
            $nearDestination = $route->isPointNearRoute($toLat, $toLng, $tolerance);

            if ($nearOrigin && $nearDestination) {
                // Find closest points on route
                $closestToOrigin = $route->findClosestPoint($fromLat, $fromLng);
                $closestToDestination = $route->findClosestPoint($toLat, $toLng);

                // Calculate walking distances
                $walkToRoute = $closestToOrigin['distance'];
                $walkFromRoute = $closestToDestination['distance'];

                // Calculate ride distance (between boarding and alighting points)
                $rideDistance = $this->calculateRideDistance($route, $closestToOrigin['index'], $closestToDestination['index']);

                // Calculate estimated fare
                $estimatedFare = $this->calculateFare($rideDistance);

                $matchingRoutes[] = [
                    'route' => [
                        'id' => $route->id,
                        'route_number' => $route->route_number,
                        'name' => $route->name,
                        'path' => $route->path,
                        'color' => $route->color,
                        'total_distance' => $route->total_distance,
                        'estimated_time' => $route->estimated_time
                    ],
                    'boarding_point' => [
                        'lat' => $closestToOrigin['lat'],
                        'lng' => $closestToOrigin['lng'],
                        'walk_distance_km' => round($walkToRoute, 2),
                        'walk_time_min' => round($walkToRoute * 12, 0) // ~5km/h walking
                    ],
                    'alighting_point' => [
                        'lat' => $closestToDestination['lat'],
                        'lng' => $closestToDestination['lng'],
                        'walk_distance_km' => round($walkFromRoute, 2),
                        'walk_time_min' => round($walkFromRoute * 12, 0)
                    ],
                    'ride_distance_km' => round($rideDistance, 2),
                    'total_walking_km' => round($walkToRoute + $walkFromRoute, 2),
                    'estimated_fare' => $estimatedFare,
                    'relevance_score' => $this->calculateRelevance($walkToRoute, $walkFromRoute, $rideDistance)
                ];
            }
        }

        // Sort by relevance (less walking = better)
        usort($matchingRoutes, function ($a, $b) {
            return $a['relevance_score'] <=> $b['relevance_score'];
        });

        return response()->json([
            'success' => true,
            'origin' => ['lat' => $fromLat, 'lng' => $fromLng],
            'destination' => ['lat' => $toLat, 'lng' => $toLng],
            'tolerance_km' => $tolerance,
            'routes_found' => count($matchingRoutes),
            'data' => $matchingRoutes
        ]);
    }

    /**
     * Calculate distance of ride along route path
     */
    private function calculateRideDistance(JeepneyRoute $route, int $startIndex, int $endIndex): float
    {
        if ($startIndex === $endIndex) return 0;

        $path = $route->path;
        $distance = 0;

        // Ensure we go from smaller to larger index
        $from = min($startIndex, $endIndex);
        $to = max($startIndex, $endIndex);

        for ($i = $from; $i < $to; $i++) {
            $distance += $route->haversineDistance(
                $path[$i]['lat'],
                $path[$i]['lng'],
                $path[$i + 1]['lat'],
                $path[$i + 1]['lng']
            );
        }

        return $distance;
    }

    /**
     * Calculate fare based on distance
     */
    private function calculateFare(float $distance): array
    {
        $baseFare = 13.00;
        $perKmRate = 1.80;
        $freeKm = 4;

        if ($distance <= $freeKm) {
            $fare = $baseFare;
            $additionalFare = 0;
        } else {
            $additionalKm = $distance - $freeKm;
            $additionalFare = round($additionalKm * $perKmRate, 2);
            $fare = $baseFare + $additionalFare;
        }

        return [
            'regular' => round($fare, 2),
            'student' => round($fare * 0.80, 2), // 20% discount
            'senior' => round($fare * 0.80, 2),  // 20% discount
            'breakdown' => [
                'base_fare' => $baseFare,
                'additional_fare' => $additionalFare,
                'distance_charged' => max(0, $distance - $freeKm)
            ]
        ];
    }

    /**
     * Calculate route relevance score (lower is better)
     */
    private function calculateRelevance($walkToRoute, $walkFromRoute, $rideDistance): float
    {
        // Walking is penalized more heavily
        $walkingPenalty = ($walkToRoute + $walkFromRoute) * 3;

        // Slight preference for shorter rides
        $distanceFactor = $rideDistance * 0.1;

        return round($walkingPenalty + $distanceFactor, 4);
    }

    /**
     * Get all route paths for map display
     */
    public function getAllPaths()
    {
        $routes = JeepneyRoute::where('status', 'available')
            ->select('id', 'route_number', 'name', 'path', 'color')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $routes
        ]);
    }
}
