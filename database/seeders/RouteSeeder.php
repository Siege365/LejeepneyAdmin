<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JeepneyRoute;

class RouteSeeder extends Seeder
{
    /**
     * Seed sample jeepney routes for Davao City
     * 
     * Data structure for Flutter mobile app:
     * - route_number: Unique route identifier (e.g., "01A", "02B")
     * - name: Route name/description
     * - terminal: Main terminal location
     * - start_point: Starting point of route
     * - end_point: Ending point of route
     * - path: Array of coordinates [{lat, lng}] defining the route path
     * - waypoints: Optional array of named waypoints along the route
     * - total_distance: Total route distance in kilometers
     * - estimated_time: Estimated travel time in minutes
     * - fare: Base fare in PHP
     * - status: 'available' or 'unavailable'
     * - color: Hex color code for route display on map
     * - description: Additional route information
     */
    public function run(): void
    {
        $routes = [
            [
                'route_number' => '01A',
                'name' => 'Bankerohan - Matina',
                'terminal' => 'Bankerohan Public Market',
                'start_point' => 'Bankerohan Public Market',
                'end_point' => 'Matina Town Square',
                'path' => [
                    ['lat' => 7.0731, 'lng' => 125.6128],  // Bankerohan
                    ['lat' => 7.0725, 'lng' => 125.6145],
                    ['lat' => 7.0715, 'lng' => 125.6165],
                    ['lat' => 7.0705, 'lng' => 125.6185],
                    ['lat' => 7.0695, 'lng' => 125.6205],
                    ['lat' => 7.0685, 'lng' => 125.6225],  // Matina
                ],
                'waypoints' => [
                    ['name' => 'Bankerohan Public Market', 'lat' => 7.0731, 'lng' => 125.6128],
                    ['name' => 'SM Lanang Premier', 'lat' => 7.0715, 'lng' => 125.6165],
                    ['name' => 'Matina Town Square', 'lat' => 7.0685, 'lng' => 125.6225],
                ],
                'total_distance' => 8.5,
                'estimated_time' => 25,
                'fare' => 13.00,
                'status' => 'available',
                'color' => '#EBAF3E',
                'description' => 'Main route connecting Bankerohan Public Market to Matina area, passing through SM Lanang Premier and major commercial establishments.',
            ],
            [
                'route_number' => '02B',
                'name' => 'Toril - City Center',
                'terminal' => 'Toril Terminal',
                'start_point' => 'Toril Terminal',
                'end_point' => 'People\'s Park',
                'path' => [
                    ['lat' => 7.0055, 'lng' => 125.4850],  // Toril
                    ['lat' => 7.0165, 'lng' => 125.4965],
                    ['lat' => 7.0275, 'lng' => 125.5080],
                    ['lat' => 7.0385, 'lng' => 125.5195],
                    ['lat' => 7.0495, 'lng' => 125.5310],
                    ['lat' => 7.0605, 'lng' => 125.5425],
                    ['lat' => 7.0715, 'lng' => 125.6040],  // City Center
                ],
                'waypoints' => [
                    ['name' => 'Toril Terminal', 'lat' => 7.0055, 'lng' => 125.4850],
                    ['name' => 'Toril Market', 'lat' => 7.0165, 'lng' => 125.4965],
                    ['name' => 'Sta. Ana Avenue', 'lat' => 7.0495, 'lng' => 125.5310],
                    ['name' => 'People\'s Park', 'lat' => 7.0715, 'lng' => 125.6040],
                ],
                'total_distance' => 15.2,
                'estimated_time' => 45,
                'fare' => 33.16,  // Base fare + additional km
                'status' => 'available',
                'color' => '#3B82F6',
                'description' => 'Long route from Toril terminal to downtown area, serving southern districts of Davao City.',
            ],
            [
                'route_number' => '03C',
                'name' => 'Agdao - Buhangin Circular',
                'terminal' => 'Agdao Public Market',
                'start_point' => 'Agdao Public Market',
                'end_point' => 'Agdao Public Market',  // Circular route
                'path' => [
                    ['lat' => 7.0825, 'lng' => 125.6350],  // Agdao
                    ['lat' => 7.0875, 'lng' => 125.6400],
                    ['lat' => 7.0925, 'lng' => 125.6450],  // Buhangin
                    ['lat' => 7.0875, 'lng' => 125.6400],
                    ['lat' => 7.0825, 'lng' => 125.6350],  // Back to Agdao
                ],
                'waypoints' => [
                    ['name' => 'Agdao Public Market', 'lat' => 7.0825, 'lng' => 125.6350],
                    ['name' => 'Buhangin Town Center', 'lat' => 7.0925, 'lng' => 125.6450],
                ],
                'total_distance' => 6.3,
                'estimated_time' => 20,
                'fare' => 13.00,
                'status' => 'available',
                'color' => '#10B981',
                'description' => 'Circular route serving Agdao and Buhangin districts. Returns to starting point.',
            ],
        ];

        foreach ($routes as $routeData) {
            JeepneyRoute::create($routeData);
        }
    }
}

