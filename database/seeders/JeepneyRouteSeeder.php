<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JeepneyRoute;

class JeepneyRouteSeeder extends Seeder
{
    /**
     * Seed jeepney routes from exported JSON data.
     * 
     * This seeder reads route data from routes_data.json which was exported
     * from the existing database. Run this to restore routes after a fresh migration.
     * 
     * Usage: php artisan db:seed --class=JeepneyRouteSeeder
     */
    public function run(): void
    {
        $jsonPath = database_path('seeders/routes_data.json');
        
        if (!file_exists($jsonPath)) {
            $this->command->error('routes_data.json not found! Please export route data first.');
            return;
        }

        $routes = json_decode(file_get_contents($jsonPath), true);

        if (empty($routes)) {
            $this->command->warn('No routes found in routes_data.json');
            return;
        }

        $count = count($routes);
        $this->command->info("Seeding {$count} jeepney routes...");

        foreach ($routes as $routeData) {
            JeepneyRoute::updateOrCreate(
                ['name' => $routeData['name']],
                [
                    'terminal' => $routeData['terminal'],
                    'path' => $routeData['path'],
                    'waypoints' => $routeData['waypoints'] ?? null,
                    'total_distance' => $routeData['total_distance'],
                    'status' => $routeData['status'] ?? 'available',
                    'color' => $routeData['color'] ?? '#EBAF3E',
                    'description' => $routeData['description'] ?? null,
                ]
            );
        }

        $this->command->info("Successfully seeded {$count} jeepney routes!");
    }
}
