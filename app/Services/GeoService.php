<?php

namespace App\Services;

class GeoService
{
    /**
     * Earth radius in kilometers
     */
    const EARTH_RADIUS_KM = 6371;

    /**
     * Haversine distance between two points in kilometers (precise)
     */
    public static function haversineDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return self::EARTH_RADIUS_KM * $c;
    }

    /**
     * Quick approximate distance in km (equirectangular projection)
     * Much faster than haversine — accurate enough for short distances (<10km)
     */
    public static function quickDistanceKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $x = ($lng2 - $lng1) * cos(deg2rad(($lat1 + $lat2) / 2));
        $y = $lat2 - $lat1;
        return 111.32 * sqrt($x * $x + $y * $y);
    }

    /**
     * Find closest point on a path to a given coordinate
     *
     * @param  array  $point  ['lat' => float, 'lng' => float]
     * @param  array  $path   Array of ['lat' => float, 'lng' => float]
     * @return array|null     ['point' => [...], 'distance' => float, 'index' => int]
     */
    public static function findClosestPointOnPath(array $point, array $path): ?array
    {
        if (empty($path)) return null;

        $closestPoint = null;
        $minDistance = PHP_FLOAT_MAX;
        $closestIndex = 0;

        foreach ($path as $index => $pathPoint) {
            $distance = self::quickDistanceKm(
                $point['lat'], $point['lng'],
                $pathPoint['lat'], $pathPoint['lng']
            );

            if ($distance < $minDistance) {
                $minDistance = $distance;
                $closestPoint = $pathPoint;
                $closestIndex = $index;
            }
        }

        // Refine with haversine for the final result
        $minDistance = self::haversineDistance(
            $point['lat'], $point['lng'],
            $closestPoint['lat'], $closestPoint['lng']
        );

        return [
            'point' => $closestPoint,
            'distance' => round($minDistance, 4),
            'index' => $closestIndex,
        ];
    }

    /**
     * Check if travel direction is forward on a path
     */
    public static function isForwardTravel(int $boardIndex, int $alightIndex): bool
    {
        return $boardIndex < $alightIndex;
    }

    /**
     * Extract sub-path between two indices (inclusive)
     */
    public static function extractSubPath(array $path, int $fromIndex, int $toIndex): array
    {
        if ($fromIndex > $toIndex || $fromIndex < 0 || $toIndex >= count($path)) {
            return [];
        }

        return array_values(array_slice($path, $fromIndex, $toIndex - $fromIndex + 1));
    }

    /**
     * Calculate total distance along a path of coordinates in km
     */
    public static function pathDistance(array $path): float
    {
        if (count($path) < 2) return 0;

        $distance = 0;
        for ($i = 0; $i < count($path) - 1; $i++) {
            $distance += self::haversineDistance(
                $path[$i]['lat'], $path[$i]['lng'],
                $path[$i + 1]['lat'], $path[$i + 1]['lng']
            );
        }

        return round($distance, 4);
    }

    /**
     * Get bounding box of a route path
     *
     * @return array ['min_lat', 'max_lat', 'min_lng', 'max_lng']
     */
    public static function getBoundingBox(array $path): array
    {
        $lats = array_column($path, 'lat');
        $lngs = array_column($path, 'lng');

        return [
            'min_lat' => min($lats),
            'max_lat' => max($lats),
            'min_lng' => min($lngs),
            'max_lng' => max($lngs),
        ];
    }

    /**
     * Check if two bounding boxes overlap with optional padding (in degrees)
     */
    public static function boundingBoxesOverlap(array $bb1, array $bb2, float $padding = 0.01): bool
    {
        return !(
            $bb1['max_lat'] + $padding < $bb2['min_lat'] - $padding ||
            $bb1['min_lat'] - $padding > $bb2['max_lat'] + $padding ||
            $bb1['max_lng'] + $padding < $bb2['min_lng'] - $padding ||
            $bb1['min_lng'] - $padding > $bb2['max_lng'] + $padding
        );
    }

    /**
     * Find the closest pair of points between two route paths within maxDistance.
     * Uses coarse-to-fine sampling for performance.
     *
     * @return array|null ['pointA' => [...], 'indexA' => int, 'pointB' => [...], 'indexB' => int, 'distance' => float]
     */
    public static function findRouteIntersection(array $pathA, array $pathB, float $maxDistance = 0.3): ?array
    {
        if (empty($pathA) || empty($pathB)) return null;

        $bestMatch = null;
        $minDist = PHP_FLOAT_MAX;

        $countA = count($pathA);
        $countB = count($pathB);

        // Coarse pass: sample every Nth point (aim for ~60 samples per path)
        $stepA = max(1, intdiv($countA, 60));
        $stepB = max(1, intdiv($countB, 60));

        for ($i = 0; $i < $countA; $i += $stepA) {
            for ($j = 0; $j < $countB; $j += $stepB) {
                $dist = self::quickDistanceKm(
                    $pathA[$i]['lat'], $pathA[$i]['lng'],
                    $pathB[$j]['lat'], $pathB[$j]['lng']
                );

                if ($dist <= $maxDistance && $dist < $minDist) {
                    $minDist = $dist;
                    $bestMatch = [
                        'pointA' => $pathA[$i],
                        'indexA' => $i,
                        'pointB' => $pathB[$j],
                        'indexB' => $j,
                        'distance' => $dist,
                    ];
                }
            }
        }

        // Fine pass: refine around the best coarse match
        if ($bestMatch && ($stepA > 1 || $stepB > 1)) {
            $rangeAStart = max(0, $bestMatch['indexA'] - $stepA * 2);
            $rangeAEnd   = min($countA - 1, $bestMatch['indexA'] + $stepA * 2);
            $rangeBStart = max(0, $bestMatch['indexB'] - $stepB * 2);
            $rangeBEnd   = min($countB - 1, $bestMatch['indexB'] + $stepB * 2);

            for ($i = $rangeAStart; $i <= $rangeAEnd; $i++) {
                for ($j = $rangeBStart; $j <= $rangeBEnd; $j++) {
                    $dist = self::quickDistanceKm(
                        $pathA[$i]['lat'], $pathA[$i]['lng'],
                        $pathB[$j]['lat'], $pathB[$j]['lng']
                    );

                    if ($dist < $minDist) {
                        $minDist = $dist;
                        $bestMatch = [
                            'pointA' => $pathA[$i],
                            'indexA' => $i,
                            'pointB' => $pathB[$j],
                            'indexB' => $j,
                            'distance' => $dist,
                        ];
                    }
                }
            }
        }

        // Final: refine the best distance with accurate haversine
        if ($bestMatch) {
            $bestMatch['distance'] = round(self::haversineDistance(
                $bestMatch['pointA']['lat'], $bestMatch['pointA']['lng'],
                $bestMatch['pointB']['lat'], $bestMatch['pointB']['lng']
            ), 4);

            // Re-check with accurate distance
            if ($bestMatch['distance'] > $maxDistance) {
                return null;
            }
        }

        return $bestMatch;
    }

    /**
     * Estimate walking time in minutes given distance in km (~5 km/h)
     */
    public static function walkingTimeMinutes(float $distanceKm): int
    {
        return (int) round($distanceKm * 12); // 12 min per km = 5 km/h
    }

    /**
     * Estimate jeepney riding time in minutes given distance in km (~15 km/h avg)
     */
    public static function ridingTimeMinutes(float $distanceKm): int
    {
        return max(1, (int) round($distanceKm * 4)); // 4 min per km = 15 km/h
    }
}
