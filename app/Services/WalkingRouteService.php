<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WalkingRouteService
{
    /**
     * Get walking route between two points.
     * Uses OpenRouteService as primary, OSRM as fallback.
     * Results are cached for 1 hour.
     */
    public function getWalkingRoute(float $fromLat, float $fromLng, float $toLat, float $toLng): ?array
    {
        $cacheKey = $this->getCacheKey($fromLat, $fromLng, $toLat, $toLng);

        return Cache::remember($cacheKey, 3600, function () use ($fromLat, $fromLng, $toLat, $toLng) {
            // Try OpenRouteService first
            $result = $this->callORS($fromLat, $fromLng, $toLat, $toLng);
            if ($result) return $result;

            // Fallback to OSRM
            return $this->callOSRM($fromLat, $fromLng, $toLat, $toLng);
        });
    }

    /**
     * Call OpenRouteService foot-walking directions API
     */
    protected function callORS(float $fromLat, float $fromLng, float $toLat, float $toLng): ?array
    {
        $apiKey = config('services.openrouteservice.key');
        if (!$apiKey) return null;

        try {
            $response = Http::withHeaders([
                'Authorization' => $apiKey,
            ])->timeout(10)->get('https://api.openrouteservice.org/v2/directions/foot-walking', [
                'start' => "{$fromLng},{$fromLat}",
                'end'   => "{$toLng},{$toLat}",
            ]);

            if (!$response->successful()) return null;

            $data = $response->json();
            $feature = $data['features'][0] ?? null;
            if (!$feature) return null;

            $geometry   = $feature['geometry']['coordinates'] ?? [];
            $properties = $feature['properties']['segments'][0] ?? [];

            $path = array_map(fn($coord) => [
                'lat' => $coord[1],
                'lng' => $coord[0],
            ], $geometry);

            return [
                'path'             => $path,
                'distance_km'      => round(($properties['distance'] ?? 0) / 1000, 3),
                'duration_minutes' => max(1, (int) round(($properties['duration'] ?? 0) / 60)),
            ];
        } catch (\Exception $e) {
            Log::warning('ORS walking route failed', [
                'error' => $e->getMessage(),
                'from'  => [$fromLat, $fromLng],
                'to'    => [$toLat, $toLng],
            ]);
            return null;
        }
    }

    /**
     * Call OSRM public foot-walking directions API (fallback)
     */
    protected function callOSRM(float $fromLat, float $fromLng, float $toLat, float $toLng): ?array
    {
        try {
            $url = sprintf(
                'https://router.project-osrm.org/route/v1/foot/%s,%s;%s,%s',
                $fromLng, $fromLat, $toLng, $toLat
            );

            $response = Http::timeout(10)->get($url, [
                'overview'   => 'full',
                'geometries' => 'geojson',
            ]);

            if (!$response->successful()) return null;

            $data = $response->json();
            if (($data['code'] ?? '') !== 'Ok') return null;

            $route = $data['routes'][0] ?? null;
            if (!$route) return null;

            $geometry = $route['geometry']['coordinates'] ?? [];

            $path = array_map(fn($coord) => [
                'lat' => $coord[1],
                'lng' => $coord[0],
            ], $geometry);

            return [
                'path'             => $path,
                'distance_km'      => round(($route['distance'] ?? 0) / 1000, 3),
                'duration_minutes' => max(1, (int) round(($route['duration'] ?? 0) / 60)),
            ];
        } catch (\Exception $e) {
            Log::warning('OSRM walking route failed', [
                'error' => $e->getMessage(),
                'from'  => [$fromLat, $fromLng],
                'to'    => [$toLat, $toLng],
            ]);
            return null;
        }
    }

    /**
     * Generate cache key from coordinates (rounded to 5 decimal places)
     */
    protected function getCacheKey(float $fromLat, float $fromLng, float $toLat, float $toLng): string
    {
        return sprintf(
            'walking_route:%s_%s_%s_%s',
            round($fromLat, 5),
            round($fromLng, 5),
            round($toLat, 5),
            round($toLng, 5)
        );
    }
}
