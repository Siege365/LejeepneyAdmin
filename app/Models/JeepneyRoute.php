<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JeepneyRoute extends Model
{
    use HasFactory;

    /**
     * Base fare for all jeepney routes (first 4km)
     */
    const BASE_FARE = 13.00;
    
    /**
     * Additional fare per kilometer after first 4km
     */
    const FARE_PER_KM = 1.80;
    
    /**
     * First kilometers covered by base fare
     */
    const BASE_DISTANCE_KM = 4;

    protected $fillable = [
        'route_number',
        'name',
        'terminal',
        'start_point',
        'end_point',
        'path',
        'waypoints',
        'total_distance',
        'estimated_time',
        'fare',
        'status',
        'color',
        'description'
    ];

    protected $casts = [
        'path' => 'array',
        'waypoints' => 'array',
        'total_distance' => 'decimal:2',
        'fare' => 'decimal:2',
        'estimated_time' => 'integer'
    ];

    /**
     * Calculate fare based on distance
     */
    public function calculateFare(float $distance = null): float
    {
        $distance = $distance ?? $this->total_distance ?? 0;
        
        if ($distance <= self::BASE_DISTANCE_KM) {
            return self::BASE_FARE;
        }
        
        $additionalKm = $distance - self::BASE_DISTANCE_KM;
        return self::BASE_FARE + ($additionalKm * self::FARE_PER_KM);
    }

    /**
     * Calculate total distance from path coordinates
     */
    public function calculateDistance(): float
    {
        if (!$this->path || count($this->path) < 2) {
            return 0;
        }

        $totalDistance = 0;
        $path = $this->path;

        for ($i = 0; $i < count($path) - 1; $i++) {
            $totalDistance += $this->haversineDistance(
                $path[$i]['lat'],
                $path[$i]['lng'],
                $path[$i + 1]['lat'],
                $path[$i + 1]['lng']
            );
        }

        return round($totalDistance, 2);
    }

    /**
     * Haversine formula to calculate distance between two coordinates
     */
    public function haversineDistance($lat1, $lon1, $lat2, $lon2): float
    {
        $earthRadius = 6371;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Check if a point is near the route (within tolerance)
     */
    public function isPointNearRoute($lat, $lng, $tolerance = 0.5): bool
    {
        if (!$this->path) return false;

        foreach ($this->path as $point) {
            $distance = $this->haversineDistance($lat, $lng, $point['lat'], $point['lng']);
            if ($distance <= $tolerance) {
                return true;
            }
        }

        return false;
    }

    /**
     * Find closest point on route to given coordinates
     */
    public function findClosestPoint($lat, $lng): ?array
    {
        if (!$this->path) return null;

        $closestPoint = null;
        $minDistance = PHP_FLOAT_MAX;

        foreach ($this->path as $index => $point) {
            $distance = $this->haversineDistance($lat, $lng, $point['lat'], $point['lng']);
            if ($distance < $minDistance) {
                $minDistance = $distance;
                $closestPoint = [
                    'lat' => $point['lat'],
                    'lng' => $point['lng'],
                    'index' => $index,
                    'distance' => round($distance, 3)
                ];
            }
        }

        return $closestPoint;
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClass(): string
    {
        return $this->status === 'available' ? 'badge-success' : 'badge-warning';
    }
}
