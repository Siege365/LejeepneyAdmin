<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\JeepneyRoute;
use Illuminate\Support\Str;

class RouteFinderService
{
    // ── Fare settings (loaded from DB) ──────────────────────────────
    protected float $baseFare;
    protected float $farePerKm;
    protected float $baseDistanceKm = 4.0;
    protected float $discountPercent = 0.20;

    // ── Route data (populated per request) ──────────────────────────
    protected array $routes = [];
    protected array $boundingBoxes = [];
    protected array $intersectionCache = [];

    // ── Limits ──────────────────────────────────────────────────────
    protected int $maxResults = 5;
    protected int $maxTwoTransferResults = 10;

    public function __construct()
    {
        $this->baseFare  = (float) AppSetting::get('base_fare', 13.00);
        $this->farePerKm = (float) AppSetting::get('fare_per_km', 1.80);
    }

    // ================================================================
    //  PUBLIC: Main entry point
    // ================================================================

    /**
     * Find routes between origin and destination with 0/1/2 transfer support.
     *
     * @return array  Sorted array of route suggestions (top 5)
     */
    public function findRoutes(
        float $fromLat,
        float $fromLng,
        float $toLat,
        float $toLng,
        float $tolerance = 1.0,
        float $transferWalkMax = 0.3,
        bool  $includeWalkingPaths = false,
    ): array {
        // 1. Load routes & precompute spatial data
        $this->loadRoutes();
        $this->precomputeIntersections($transferWalkMax);

        // 2. Collect results from all strategies
        $results = [];

        $results = array_merge($results, $this->findDirectRoutes(
            $fromLat, $fromLng, $toLat, $toLng, $tolerance
        ));

        $results = array_merge($results, $this->findOneTransferRoutes(
            $fromLat, $fromLng, $toLat, $toLng, $tolerance, $transferWalkMax
        ));

        // Only compute 2-transfer if we still need more options
        if (count($results) < $this->maxResults) {
            $results = array_merge($results, $this->findTwoTransferRoutes(
                $fromLat, $fromLng, $toLat, $toLng, $tolerance, $transferWalkMax
            ));
        }

        // 3. Optionally fetch real walking paths
        if ($includeWalkingPaths) {
            $this->enrichWithWalkingPaths($results);
        }

        // 4. Score, sort, return top N
        usort($results, fn($a, $b) => $a['score'] <=> $b['score']);

        return array_values(array_slice($results, 0, $this->maxResults));
    }

    // ================================================================
    //  PRIVATE: Data loading
    // ================================================================

    /**
     * Load all available routes and precompute bounding boxes.
     */
    protected function loadRoutes(): void
    {
        $this->routes = [];
        $this->boundingBoxes = [];

        $dbRoutes = JeepneyRoute::where('status', 'available')->get();

        foreach ($dbRoutes as $route) {
            $data = $route->toArray();
            $path = $data['path'];

            // Ensure path is always an array (model has 'array' cast, but be safe)
            if (is_string($path)) {
                $path = json_decode($path, true);
            }

            $data['path'] = $path ?: [];

            if (!empty($data['path'])) {
                $this->boundingBoxes[$data['id']] = GeoService::getBoundingBox($data['path']);
            }

            $this->routes[] = $data;
        }
    }

    /**
     * Precompute pairwise route intersections (used by 1- and 2-transfer finders).
     * Stores results in both directions: A→B and B→A.
     */
    protected function precomputeIntersections(float $transferWalkMax): void
    {
        $this->intersectionCache = [];
        $count = count($this->routes);

        for ($i = 0; $i < $count; $i++) {
            for ($j = $i + 1; $j < $count; $j++) {
                $routeA = $this->routes[$i];
                $routeB = $this->routes[$j];

                if (empty($routeA['path']) || empty($routeB['path'])) continue;

                // Bounding box pre-filter
                $bbA = $this->boundingBoxes[$routeA['id']] ?? null;
                $bbB = $this->boundingBoxes[$routeB['id']] ?? null;
                if ($bbA && $bbB && !GeoService::boundingBoxesOverlap($bbA, $bbB, 0.005)) continue;

                $intersection = GeoService::findRouteIntersection(
                    $routeA['path'], $routeB['path'], $transferWalkMax
                );

                if ($intersection) {
                    // A → B
                    $this->intersectionCache[$routeA['id'] . '-' . $routeB['id']] = $intersection;

                    // B → A (swap point/index references)
                    $this->intersectionCache[$routeB['id'] . '-' . $routeA['id']] = [
                        'pointA'   => $intersection['pointB'],
                        'indexA'   => $intersection['indexB'],
                        'pointB'   => $intersection['pointA'],
                        'indexB'   => $intersection['indexA'],
                        'distance' => $intersection['distance'],
                    ];
                }
            }
        }
    }

    /**
     * Look up a precomputed intersection between two route IDs.
     */
    protected function getIntersection(int $routeIdA, int $routeIdB): ?array
    {
        return $this->intersectionCache[$routeIdA . '-' . $routeIdB] ?? null;
    }

    // ================================================================
    //  STRATEGY 1: Direct routes (0 transfers)
    // ================================================================

    protected function findDirectRoutes(
        float $fromLat, float $fromLng,
        float $toLat, float $toLng,
        float $tolerance,
    ): array {
        $results = [];

        foreach ($this->routes as $route) {
            if (empty($route['path'])) continue;

            $boarding  = GeoService::findClosestPointOnPath(['lat' => $fromLat, 'lng' => $fromLng], $route['path']);
            $alighting = GeoService::findClosestPointOnPath(['lat' => $toLat, 'lng' => $toLng], $route['path']);

            if (!$boarding || !$alighting) continue;
            if ($boarding['distance'] > $tolerance) continue;
            if ($alighting['distance'] > $tolerance) continue;
            if (!GeoService::isForwardTravel($boarding['index'], $alighting['index'])) continue;

            // Calculate distances
            $rideSubPath    = GeoService::extractSubPath($route['path'], $boarding['index'], $alighting['index']);
            $rideDistance    = GeoService::pathDistance($rideSubPath);
            $fare            = $this->calculateFare($rideDistance);
            $walkToDistance  = $boarding['distance'];
            $walkFromDistance = $alighting['distance'];
            $totalWalking    = $walkToDistance + $walkFromDistance;
            $rideTime        = GeoService::ridingTimeMinutes($rideDistance);
            $walkTime        = GeoService::walkingTimeMinutes($totalWalking);
            $totalTime       = $rideTime + $walkTime;

            // Build segments
            $segments = [];

            $segments[] = $this->buildWalkingSegment(
                $fromLat, $fromLng,
                $boarding['point']['lat'], $boarding['point']['lng'],
                $walkToDistance, 'Your Location', 'Boarding Point'
            );

            $segments[] = $this->buildRideSegment(
                $route,
                $boarding['point'], $alighting['point'],
                $rideSubPath, $rideDistance, $fare,
                $rideTime, 'Boarding Point', 'Alighting Point'
            );

            $segments[] = $this->buildWalkingSegment(
                $alighting['point']['lat'], $alighting['point']['lng'],
                $toLat, $toLng,
                $walkFromDistance, 'Alighting Point', 'Your Destination'
            );

            $score = $this->calculateScore(0, $fare, $totalWalking, $totalTime);

            $results[] = $this->buildSuggestion(
                transferCount: 0,
                totalFare: $fare,
                rideDistances: [$rideDistance],
                totalWalking: $totalWalking,
                totalTime: $totalTime,
                score: $score,
                segments: $segments,
                fares: [$fare],
            );
        }

        return $results;
    }

    // ================================================================
    //  STRATEGY 2: One-transfer routes
    // ================================================================

    protected function findOneTransferRoutes(
        float $fromLat, float $fromLng,
        float $toLat, float $toLng,
        float $tolerance,
        float $transferWalkMax,
    ): array {
        $results = [];

        // Pre-filter: which routes are close to origin / destination
        $nearOrigin = [];
        $nearDest   = [];

        foreach ($this->routes as $route) {
            if (empty($route['path'])) continue;

            $closestO = GeoService::findClosestPointOnPath(['lat' => $fromLat, 'lng' => $fromLng], $route['path']);
            $closestD = GeoService::findClosestPointOnPath(['lat' => $toLat, 'lng' => $toLng], $route['path']);

            if ($closestO && $closestO['distance'] <= $tolerance) {
                $nearOrigin[] = ['route' => $route, 'boarding' => $closestO];
            }
            if ($closestD && $closestD['distance'] <= $tolerance) {
                $nearDest[] = ['route' => $route, 'alighting' => $closestD];
            }
        }

        foreach ($nearOrigin as $a) {
            foreach ($nearDest as $b) {
                if ($a['route']['id'] === $b['route']['id']) continue;

                // Look up precomputed intersection
                $intersection = $this->getIntersection($a['route']['id'], $b['route']['id']);
                if (!$intersection) continue;

                // Forward travel checks
                if (!GeoService::isForwardTravel($a['boarding']['index'], $intersection['indexA'])) continue;
                if (!GeoService::isForwardTravel($intersection['indexB'], $b['alighting']['index'])) continue;

                // Calculate ride segments
                $rideSubPathA = GeoService::extractSubPath($a['route']['path'], $a['boarding']['index'], $intersection['indexA']);
                $rideSubPathB = GeoService::extractSubPath($b['route']['path'], $intersection['indexB'], $b['alighting']['index']);

                $rideDistA = GeoService::pathDistance($rideSubPathA);
                $rideDistB = GeoService::pathDistance($rideSubPathB);

                $fareA = $this->calculateFare($rideDistA);
                $fareB = $this->calculateFare($rideDistB);
                $totalFare = $fareA + $fareB;

                $walkToDistance   = $a['boarding']['distance'];
                $walkTransfer     = $intersection['distance'];
                $walkFromDistance = $b['alighting']['distance'];
                $totalWalking     = $walkToDistance + $walkTransfer + $walkFromDistance;

                $rideTimeA = GeoService::ridingTimeMinutes($rideDistA);
                $rideTimeB = GeoService::ridingTimeMinutes($rideDistB);
                $walkTime  = GeoService::walkingTimeMinutes($totalWalking);
                $totalTime = $rideTimeA + $rideTimeB + $walkTime;

                // Build segments
                $segments = [];

                // Walk → Board A
                $segments[] = $this->buildWalkingSegment(
                    $fromLat, $fromLng,
                    $a['boarding']['point']['lat'], $a['boarding']['point']['lng'],
                    $walkToDistance, 'Your Location', 'Boarding Point'
                );

                // Ride A
                $segments[] = $this->buildRideSegment(
                    $a['route'],
                    $a['boarding']['point'], $intersection['pointA'],
                    $rideSubPathA, $rideDistA, $fareA,
                    $rideTimeA, 'Boarding Point', 'Transfer Point'
                );

                // Walk transfer
                $segments[] = $this->buildWalkingSegment(
                    $intersection['pointA']['lat'], $intersection['pointA']['lng'],
                    $intersection['pointB']['lat'], $intersection['pointB']['lng'],
                    $walkTransfer,
                    'Alight from ' . $a['route']['route_number'],
                    'Board ' . $b['route']['route_number']
                );

                // Ride B
                $segments[] = $this->buildRideSegment(
                    $b['route'],
                    $intersection['pointB'], $b['alighting']['point'],
                    $rideSubPathB, $rideDistB, $fareB,
                    $rideTimeB, 'Transfer Point', 'Alighting Point'
                );

                // Walk → Destination
                $segments[] = $this->buildWalkingSegment(
                    $b['alighting']['point']['lat'], $b['alighting']['point']['lng'],
                    $toLat, $toLng,
                    $walkFromDistance, 'Alighting Point', 'Your Destination'
                );

                $score = $this->calculateScore(1, $totalFare, $totalWalking, $totalTime);

                $results[] = $this->buildSuggestion(
                    transferCount: 1,
                    totalFare: $totalFare,
                    rideDistances: [$rideDistA, $rideDistB],
                    totalWalking: $totalWalking,
                    totalTime: $totalTime,
                    score: $score,
                    segments: $segments,
                    fares: [$fareA, $fareB],
                );
            }
        }

        return $results;
    }

    // ================================================================
    //  STRATEGY 3: Two-transfer routes
    // ================================================================

    protected function findTwoTransferRoutes(
        float $fromLat, float $fromLng,
        float $toLat, float $toLng,
        float $tolerance,
        float $transferWalkMax,
    ): array {
        $results = [];

        // Pre-filter: routes near endpoints
        $nearOrigin = [];
        $nearDest   = [];

        foreach ($this->routes as $route) {
            if (empty($route['path'])) continue;

            $closestO = GeoService::findClosestPointOnPath(['lat' => $fromLat, 'lng' => $fromLng], $route['path']);
            $closestD = GeoService::findClosestPointOnPath(['lat' => $toLat, 'lng' => $toLng], $route['path']);

            if ($closestO && $closestO['distance'] <= $tolerance) {
                $nearOrigin[] = ['route' => $route, 'boarding' => $closestO];
            }
            if ($closestD && $closestD['distance'] <= $tolerance) {
                $nearDest[] = ['route' => $route, 'alighting' => $closestD];
            }
        }

        foreach ($nearOrigin as $a) {
            foreach ($nearDest as $c) {
                if ($a['route']['id'] === $c['route']['id']) continue;

                // Try each middle route B
                foreach ($this->routes as $bRoute) {
                    if (empty($bRoute['path'])) continue;
                    if ($bRoute['id'] === $a['route']['id']) continue;
                    if ($bRoute['id'] === $c['route']['id']) continue;

                    // Look up precomputed intersections
                    $intAB = $this->getIntersection($a['route']['id'], $bRoute['id']);
                    if (!$intAB) continue;

                    $intBC = $this->getIntersection($bRoute['id'], $c['route']['id']);
                    if (!$intBC) continue;

                    // Forward travel on all three routes
                    if (!GeoService::isForwardTravel($a['boarding']['index'], $intAB['indexA'])) continue;
                    if (!GeoService::isForwardTravel($intAB['indexB'], $intBC['indexA'])) continue;
                    if (!GeoService::isForwardTravel($intBC['indexB'], $c['alighting']['index'])) continue;

                    // Calculate ride segments
                    $rideSubA = GeoService::extractSubPath($a['route']['path'], $a['boarding']['index'], $intAB['indexA']);
                    $rideSubB = GeoService::extractSubPath($bRoute['path'], $intAB['indexB'], $intBC['indexA']);
                    $rideSubC = GeoService::extractSubPath($c['route']['path'], $intBC['indexB'], $c['alighting']['index']);

                    $rideDistA = GeoService::pathDistance($rideSubA);
                    $rideDistB = GeoService::pathDistance($rideSubB);
                    $rideDistC = GeoService::pathDistance($rideSubC);

                    $fareA = $this->calculateFare($rideDistA);
                    $fareB = $this->calculateFare($rideDistB);
                    $fareC = $this->calculateFare($rideDistC);
                    $totalFare = $fareA + $fareB + $fareC;

                    $walkTo        = $a['boarding']['distance'];
                    $walkTransfer1 = $intAB['distance'];
                    $walkTransfer2 = $intBC['distance'];
                    $walkFrom      = $c['alighting']['distance'];
                    $totalWalking  = $walkTo + $walkTransfer1 + $walkTransfer2 + $walkFrom;

                    $rideTimeA = GeoService::ridingTimeMinutes($rideDistA);
                    $rideTimeB = GeoService::ridingTimeMinutes($rideDistB);
                    $rideTimeC = GeoService::ridingTimeMinutes($rideDistC);
                    $walkTime  = GeoService::walkingTimeMinutes($totalWalking);
                    $totalTime = $rideTimeA + $rideTimeB + $rideTimeC + $walkTime;

                    // Build segments
                    $segments = [];

                    // Walk → Board A
                    $segments[] = $this->buildWalkingSegment(
                        $fromLat, $fromLng,
                        $a['boarding']['point']['lat'], $a['boarding']['point']['lng'],
                        $walkTo, 'Your Location', 'Boarding Point'
                    );

                    // Ride A
                    $segments[] = $this->buildRideSegment(
                        $a['route'],
                        $a['boarding']['point'], $intAB['pointA'],
                        $rideSubA, $rideDistA, $fareA,
                        $rideTimeA, 'Boarding Point', 'Transfer Point 1'
                    );

                    // Walk transfer 1
                    $segments[] = $this->buildWalkingSegment(
                        $intAB['pointA']['lat'], $intAB['pointA']['lng'],
                        $intAB['pointB']['lat'], $intAB['pointB']['lng'],
                        $walkTransfer1,
                        'Alight from ' . $a['route']['route_number'],
                        'Board ' . $bRoute['route_number']
                    );

                    // Ride B
                    $segments[] = $this->buildRideSegment(
                        $bRoute,
                        $intAB['pointB'], $intBC['pointA'],
                        $rideSubB, $rideDistB, $fareB,
                        $rideTimeB, 'Transfer Point 1', 'Transfer Point 2'
                    );

                    // Walk transfer 2
                    $segments[] = $this->buildWalkingSegment(
                        $intBC['pointA']['lat'], $intBC['pointA']['lng'],
                        $intBC['pointB']['lat'], $intBC['pointB']['lng'],
                        $walkTransfer2,
                        'Alight from ' . $bRoute['route_number'],
                        'Board ' . $c['route']['route_number']
                    );

                    // Ride C
                    $segments[] = $this->buildRideSegment(
                        $c['route'],
                        $intBC['pointB'], $c['alighting']['point'],
                        $rideSubC, $rideDistC, $fareC,
                        $rideTimeC, 'Transfer Point 2', 'Alighting Point'
                    );

                    // Walk → Destination
                    $segments[] = $this->buildWalkingSegment(
                        $c['alighting']['point']['lat'], $c['alighting']['point']['lng'],
                        $toLat, $toLng,
                        $walkFrom, 'Alighting Point', 'Your Destination'
                    );

                    $score = $this->calculateScore(2, $totalFare, $totalWalking, $totalTime);

                    $results[] = $this->buildSuggestion(
                        transferCount: 2,
                        totalFare: $totalFare,
                        rideDistances: [$rideDistA, $rideDistB, $rideDistC],
                        totalWalking: $totalWalking,
                        totalTime: $totalTime,
                        score: $score,
                        segments: $segments,
                        fares: [$fareA, $fareB, $fareC],
                    );

                    // Cap 2-transfer results to avoid combinatorial explosion
                    if (count($results) >= $this->maxTwoTransferResults) break;
                }
                if (count($results) >= $this->maxTwoTransferResults) break;
            }
            if (count($results) >= $this->maxTwoTransferResults) break;
        }

        return $results;
    }

    // ================================================================
    //  HELPERS: Segment builders
    // ================================================================

    protected function buildWalkingSegment(
        float $fromLat, float $fromLng,
        float $toLat, float $toLng,
        float $distance,
        string $fromName,
        string $toName,
    ): array {
        return [
            'type'                   => 'walking',
            'from'                   => ['lat' => round($fromLat, 6), 'lng' => round($fromLng, 6)],
            'to'                     => ['lat' => round($toLat, 6),   'lng' => round($toLng, 6)],
            'distance_km'            => round($distance, 3),
            'estimated_time_minutes' => GeoService::walkingTimeMinutes($distance),
            'from_name'              => $fromName,
            'to_name'                => $toName,
        ];
    }

    protected function buildRideSegment(
        array $route,
        array $fromPoint,
        array $toPoint,
        array $subPath,
        float $distance,
        float $fare,
        int   $time,
        string $fromName,
        string $toName,
    ): array {
        return [
            'type'                   => 'jeepney_ride',
            'route_id'               => $route['id'],
            'route_number'           => $route['route_number'],
            'route_name'             => $route['name'],
            'route_color'            => $route['color'] ?? '#EBAF3E',
            'from'                   => ['lat' => round($fromPoint['lat'], 6), 'lng' => round($fromPoint['lng'], 6)],
            'to'                     => ['lat' => round($toPoint['lat'], 6),   'lng' => round($toPoint['lng'], 6)],
            'path'                   => $subPath,
            'distance_km'            => round($distance, 2),
            'fare'                   => round($fare, 2),
            'estimated_time_minutes' => $time,
            'from_name'              => $fromName,
            'to_name'                => $toName,
        ];
    }

    // ================================================================
    //  HELPERS: Build final suggestion object
    // ================================================================

    protected function buildSuggestion(
        int   $transferCount,
        float $totalFare,
        array $rideDistances,
        float $totalWalking,
        int   $totalTime,
        float $score,
        array $segments,
        array $fares,
    ): array {
        $totalRideDistance = array_sum($rideDistances);

        return [
            'id'                     => Str::uuid()->toString(),
            'transfer_count'         => $transferCount,
            'total_fare'             => round($totalFare, 2),
            'total_distance_km'      => round($totalRideDistance + $totalWalking, 2),
            'total_walking_km'       => round($totalWalking, 3),
            'estimated_time_minutes' => $totalTime,
            'score'                  => round($score, 2),
            'segments'               => $segments,
            'fare_breakdown'         => [
                'regular'     => round($totalFare, 2),
                'student'     => round($totalFare * (1 - $this->discountPercent), 2),
                'senior'      => round($totalFare * (1 - $this->discountPercent), 2),
                'per_segment' => array_map(fn($f) => round($f, 2), $fares),
            ],
        ];
    }

    // ================================================================
    //  HELPERS: Calculations
    // ================================================================

    /**
     * Calculate fare for a single ride segment using DB settings.
     */
    protected function calculateFare(float $distanceKm): float
    {
        if ($distanceKm <= $this->baseDistanceKm) {
            return $this->baseFare;
        }

        return $this->baseFare + (($distanceKm - $this->baseDistanceKm) * $this->farePerKm);
    }

    /**
     * Calculate route suggestion score (lower = better).
     *
     * score = (transfers × 40) + (fare × 2) + (walking_km × 100) + (time × 1) − (direct bonus 10)
     */
    protected function calculateScore(int $transfers, float $totalFare, float $totalWalkingKm, int $timeMinutes): float
    {
        $score = ($transfers * 40)
               + ($totalFare * 2)
               + ($totalWalkingKm * 100)
               + ($timeMinutes * 1);

        if ($transfers === 0) {
            $score -= 10;
        }

        return max(0, $score);
    }

    // ================================================================
    //  OPTIONAL: Enrich walking segments with real paths
    // ================================================================

    /**
     * For each walking segment, fetch real walking directions from ORS/OSRM.
     */
    protected function enrichWithWalkingPaths(array &$results): void
    {
        $walkingService = app(WalkingRouteService::class);

        foreach ($results as &$result) {
            foreach ($result['segments'] as &$segment) {
                if ($segment['type'] === 'walking' && $segment['distance_km'] > 0.02) {
                    $walkingRoute = $walkingService->getWalkingRoute(
                        $segment['from']['lat'], $segment['from']['lng'],
                        $segment['to']['lat'],   $segment['to']['lng']
                    );
                    if ($walkingRoute) {
                        $segment['path']                   = $walkingRoute['path'];
                        $segment['distance_km']            = $walkingRoute['distance_km'];
                        $segment['estimated_time_minutes'] = $walkingRoute['duration_minutes'];
                    }
                }
            }
            unset($segment);
        }
        unset($result);
    }
}
