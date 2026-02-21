<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\WalkingRouteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WalkingRouteController extends Controller
{
    /**
     * Get walking directions between two points.
     * Proxies to OpenRouteService (primary) or OSRM (fallback).
     *
     * POST /api/v1/walking-route
     */
    public function __invoke(Request $request, WalkingRouteService $walkingService): JsonResponse
    {
        $request->validate([
            'from_lat' => 'required|numeric|between:-90,90',
            'from_lng' => 'required|numeric|between:-180,180',
            'to_lat'   => 'required|numeric|between:-90,90',
            'to_lng'   => 'required|numeric|between:-180,180',
        ]);

        $result = $walkingService->getWalkingRoute(
            $request->from_lat,
            $request->from_lng,
            $request->to_lat,
            $request->to_lng,
        );

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to fetch walking directions. Please try again later.',
            ], 503);
        }

        return response()->json([
            'success' => true,
            'data'    => $result,
        ]);
    }
}
