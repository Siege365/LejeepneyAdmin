<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Landmark;
use Illuminate\Http\Request;

class LandmarkApiController extends Controller
{
    /**
     * Get all landmarks
     * GET /api/v1/landmarks
     */
    public function index(Request $request)
    {
        $query = Landmark::query();

        // Filter by category if provided
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        // Filter by featured if provided
        if ($request->has('featured')) {
            $query->where('is_featured', $request->boolean('featured'));
        }

        // Search by name if provided
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $landmarks = $query->select(
            'id', 'name', 'category', 'description', 
            'latitude', 'longitude', 'icon_image', 
            'is_featured'
        )
        ->orderBy('category')
        ->orderBy('name')
        ->get();

        return response()->json([
            'success' => true,
            'count' => $landmarks->count(),
            'data' => $landmarks->map(function($landmark) {
                return [
                    'id' => $landmark->id,
                    'name' => $landmark->name,
                    'category' => $landmark->category,
                    'description' => $landmark->description,
                    'latitude' => (float) $landmark->latitude,
                    'longitude' => (float) $landmark->longitude,
                    'icon_url' => $landmark->icon_image ? asset('storage/' . $landmark->icon_image) : null,
                    'is_featured' => (bool) $landmark->is_featured,
                    'coordinates' => [
                        'lat' => (float) $landmark->latitude,
                        'lng' => (float) $landmark->longitude
                    ]
                ];
            })
        ]);
    }

    /**
     * Get specific landmark details
     * GET /api/v1/landmarks/{id}
     */
    public function show($id)
    {
        $landmark = Landmark::find($id);

        if (!$landmark) {
            return response()->json([
                'success' => false,
                'message' => 'Landmark not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $landmark->id,
                'name' => $landmark->name,
                'category' => $landmark->category,
                'description' => $landmark->description,
                'latitude' => (float) $landmark->latitude,
                'longitude' => (float) $landmark->longitude,
                'icon_url' => $landmark->icon_image ? asset('storage/' . $landmark->icon_image) : null,
                'is_featured' => (bool) $landmark->is_featured,
                'coordinates' => [
                    'lat' => (float) $landmark->latitude,
                    'lng' => (float) $landmark->longitude
                ]
            ]
        ]);
    }

    /**
     * Get landmarks by category
     * GET /api/v1/landmarks/category/{category}
     */
    public function byCategory($category)
    {
        $landmarks = Landmark::where('category', $category)
            ->select('id', 'name', 'category', 'description', 'latitude', 'longitude', 'icon_image', 'is_featured')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'category' => $category,
            'count' => $landmarks->count(),
            'data' => $landmarks->map(function($landmark) {
                return [
                    'id' => $landmark->id,
                    'name' => $landmark->name,
                    'category' => $landmark->category,
                    'description' => $landmark->description,
                    'latitude' => (float) $landmark->latitude,
                    'longitude' => (float) $landmark->longitude,
                    'icon_url' => $landmark->icon_image ? asset('storage/' . $landmark->icon_image) : null,
                    'is_featured' => (bool) $landmark->is_featured,
                    'coordinates' => [
                        'lat' => (float) $landmark->latitude,
                        'lng' => (float) $landmark->longitude
                    ]
                ];
            })
        ]);
    }

    /**
     * Get featured landmarks
     * GET /api/v1/landmarks/featured
     */
    public function featured()
    {
        $landmarks = Landmark::where('is_featured', true)
            ->select('id', 'name', 'category', 'description', 'latitude', 'longitude', 'icon_image')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'count' => $landmarks->count(),
            'data' => $landmarks->map(function($landmark) {
                return [
                    'id' => $landmark->id,
                    'name' => $landmark->name,
                    'category' => $landmark->category,
                    'description' => $landmark->description,
                    'latitude' => (float) $landmark->latitude,
                    'longitude' => (float) $landmark->longitude,
                    'icon_url' => $landmark->icon_image ? asset('storage/' . $landmark->icon_image) : null,
                    'coordinates' => [
                        'lat' => (float) $landmark->latitude,
                        'lng' => (float) $landmark->longitude
                    ]
                ];
            })
        ]);
    }

    /**
     * Find nearby landmarks
     * POST /api/v1/landmarks/nearby
     */
    public function nearby(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius' => 'nullable|numeric|min:0.1|max:10' // in kilometers
        ]);

        $lat = $request->latitude;
        $lng = $request->longitude;
        $radius = $request->radius ?? 2; // 2km default

        $landmarks = Landmark::all();
        $nearbyLandmarks = [];

        foreach ($landmarks as $landmark) {
            $distance = $this->calculateDistance(
                $lat, $lng,
                $landmark->latitude, $landmark->longitude
            );

            if ($distance <= $radius) {
                $nearbyLandmarks[] = [
                    'id' => $landmark->id,
                    'name' => $landmark->name,
                    'category' => $landmark->category,
                    'description' => $landmark->description,
                    'latitude' => (float) $landmark->latitude,
                    'longitude' => (float) $landmark->longitude,
                    'icon_url' => $landmark->icon_image ? asset('storage/' . $landmark->icon_image) : null,
                    'is_featured' => (bool) $landmark->is_featured,
                    'distance_km' => round($distance, 2),
                    'coordinates' => [
                        'lat' => (float) $landmark->latitude,
                        'lng' => (float) $landmark->longitude
                    ]
                ];
            }
        }

        // Sort by distance
        usort($nearbyLandmarks, function($a, $b) {
            return $a['distance_km'] <=> $b['distance_km'];
        });

        return response()->json([
            'success' => true,
            'center' => [
                'lat' => (float) $lat,
                'lng' => (float) $lng
            ],
            'radius_km' => $radius,
            'count' => count($nearbyLandmarks),
            'data' => $nearbyLandmarks
        ]);
    }

    /**
     * Calculate distance between two coordinates (Haversine formula)
     */
    private function calculateDistance($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;

        return $distance;
    }
}
