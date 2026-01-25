# Jeepney Routes API - Data Structure Guide

## Overview

This document describes the complete data structure for the Jeepney Routes API used by the Flutter mobile app.

## Database Migration Completed ✅

The missing columns (`route_number`, `start_point`, `end_point`, `estimated_time`, `fare`) have been added to the `jeepney_routes` table.

## API Endpoint

```
GET /api/v1/routes
```

## Complete Table Structure

### jeepney_routes Table

| Column         | Type         | Required | Description                                         |
| -------------- | ------------ | -------- | --------------------------------------------------- |
| id             | bigint       | Auto     | Primary key                                         |
| route_number   | varchar(50)  | Yes      | Unique route identifier (e.g., "01A", "02B")        |
| name           | varchar      | Yes      | Route name/description                              |
| terminal       | varchar      | Optional | Main terminal location                              |
| start_point    | varchar      | Optional | Starting point of route                             |
| end_point      | varchar      | Optional | Ending point of route                               |
| path           | json         | Yes      | Array of coordinates defining the route path        |
| waypoints      | json         | Optional | Array of named waypoints along the route            |
| total_distance | decimal(8,2) | Optional | Total route distance in kilometers                  |
| estimated_time | integer      | Optional | Estimated travel time in minutes                    |
| fare           | decimal(8,2) | Optional | Base fare in PHP                                    |
| status         | enum         | Yes      | 'available' or 'unavailable' (default: 'available') |
| color          | varchar(7)   | Yes      | Hex color code for map display (default: '#EBAF3E') |
| description    | text         | Optional | Additional route information                        |
| created_at     | timestamp    | Auto     | Creation timestamp                                  |
| updated_at     | timestamp    | Auto     | Last update timestamp                               |

## JSON Data Structure for Insert

### Basic Route Insert Example

```php
JeepneyRoute::create([
    'route_number' => '01A',
    'name' => 'Bankerohan - Matina',
    'terminal' => 'Bankerohan Public Market',
    'start_point' => 'Bankerohan Public Market',
    'end_point' => 'Matina Town Square',
    'path' => [
        ['lat' => 7.0731, 'lng' => 125.6128],
        ['lat' => 7.0725, 'lng' => 125.6145],
        ['lat' => 7.0715, 'lng' => 125.6165],
        ['lat' => 7.0705, 'lng' => 125.6185],
        ['lat' => 7.0685, 'lng' => 125.6225],
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
    'description' => 'Main route connecting Bankerohan to Matina area',
]);
```

### Circular Route Example

```php
JeepneyRoute::create([
    'route_number' => '03C',
    'name' => 'Agdao - Buhangin Circular',
    'terminal' => 'Agdao Public Market',
    'start_point' => 'Agdao Public Market',
    'end_point' => 'Agdao Public Market',  // Same as start for circular routes
    'path' => [
        ['lat' => 7.0825, 'lng' => 125.6350],  // Start
        ['lat' => 7.0875, 'lng' => 125.6400],
        ['lat' => 7.0925, 'lng' => 125.6450],  // Halfway point
        ['lat' => 7.0875, 'lng' => 125.6400],
        ['lat' => 7.0825, 'lng' => 125.6350],  // Return to start
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
    'description' => 'Circular route serving Agdao and Buhangin districts',
]);
```

## API Response Format

### GET /api/v1/routes

```json
{
    "success": true,
    "count": 3,
    "data": [
        {
            "id": 1,
            "route_number": "01A",
            "name": "Bankerohan - Matina",
            "path": [
                { "lat": 7.0731, "lng": 125.6128 },
                { "lat": 7.0725, "lng": 125.6145 },
                { "lat": 7.0715, "lng": 125.6165 }
            ],
            "waypoints": [
                {
                    "name": "Bankerohan Public Market",
                    "lat": 7.0731,
                    "lng": 125.6128
                },
                { "name": "SM Lanang Premier", "lat": 7.0715, "lng": 125.6165 }
            ],
            "start_point": "Bankerohan Public Market",
            "end_point": "Matina Town Square",
            "total_distance": "8.50",
            "color": "#EBAF3E",
            "estimated_time": 25,
            "fare": "13.00"
        }
    ]
}
```

## Important Notes for Flutter Development

### Path Coordinates

- **Format**: Array of objects with `lat` and `lng` keys
- **Type**: Decimal coordinates (not strings)
- **Order**: Sequential points from start to end
- **Minimum**: At least 2 points required
- **Use Case**: Draw polylines on map

### Waypoints (Optional)

- **Format**: Array of objects with `name`, `lat`, `lng`
- **Purpose**: Major stops/landmarks along the route
- **Use Case**: Display markers on map with labels

### Fare Calculation

- **Base Fare**: ₱13.00 (first 4km)
- **Additional**: ₱1.80 per km after 4km
- **Formula**:
    ```
    fare = distance <= 4 ? 13.00 : 13.00 + ((distance - 4) * 1.80)
    ```

### Route Number Conventions

- Format: `{Number}{Letter}` (e.g., "01A", "02B", "15C")
- Number: Route sequence (01-99)
- Letter: Variant (A, B, C for different variations)
- Must be unique across all routes

### Status Values

- `available`: Route is active and operational
- `unavailable`: Route is temporarily suspended

### Color Codes

- Use valid hex color codes (e.g., "#EBAF3E")
- Used for displaying route lines on maps
- Should be distinct for easy visual identification

## Sample Data Seeder

Run this command to populate sample routes:

```bash
php artisan db:seed --class=RouteSeeder
```

## Testing the API

```bash
# Test locally
curl http://localhost:8000/api/v1/routes

# Or visit in browser
http://localhost:8000/api/v1/routes
```

## Flutter Integration Example

```dart
// Model class
class JeepneyRoute {
  final int id;
  final String routeNumber;
  final String name;
  final List<LatLng> path;
  final List<Waypoint> waypoints;
  final String startPoint;
  final String endPoint;
  final double totalDistance;
  final String color;
  final int estimatedTime;
  final double fare;

  JeepneyRoute.fromJson(Map<String, dynamic> json)
      : id = json['id'],
        routeNumber = json['route_number'],
        name = json['name'],
        path = (json['path'] as List)
            .map((p) => LatLng(p['lat'], p['lng']))
            .toList(),
        waypoints = (json['waypoints'] as List?)
            ?.map((w) => Waypoint.fromJson(w))
            .toList() ?? [],
        startPoint = json['start_point'],
        endPoint = json['end_point'],
        totalDistance = double.parse(json['total_distance']),
        color = json['color'],
        estimatedTime = json['estimated_time'],
        fare = double.parse(json['fare']);
}

// API call
Future<List<JeepneyRoute>> fetchRoutes() async {
  final response = await http.get(
    Uri.parse('http://your-server.com/api/v1/routes'),
  );

  if (response.statusCode == 200) {
    final data = json.decode(response.body);
    return (data['data'] as List)
        .map((route) => JeepneyRoute.fromJson(route))
        .toList();
  }
  throw Exception('Failed to load routes');
}
```

## Troubleshooting

### Common Issues

1. **"Unknown column route_number"**
    - Solution: Run `php artisan migrate`
    - This adds the missing columns

2. **Empty API response**
    - Solution: Run `php artisan db:seed --class=RouteSeeder`
    - This populates sample data

3. **JSON parse error on path/waypoints**
    - Ensure path and waypoints are properly formatted arrays
    - Check that lat/lng are numbers, not strings
    - Use `json_encode()` when inserting from PHP

4. **Invalid coordinates**
    - Davao City coordinates range:
        - Latitude: ~6.9° to 7.3°
        - Longitude: ~125.2° to 125.7°

## Model Helper Methods

The `JeepneyRoute` model includes useful helper methods:

```php
// Calculate total distance from path coordinates
$route->calculateDistance();

// Calculate fare based on distance
$route->calculateFare($distanceInKm);

// Check if point is near route
$route->isPointNearRoute($lat, $lng, $toleranceInKm);

// Find closest point on route
$route->findClosestPoint($lat, $lng);
```

## Migration Files Reference

1. **Initial creation**: `2026_01_20_000001_create_jeepney_routes_table.php`
2. **Simplification**: `2026_01_20_000002_simplify_jeepney_routes_table.php`
3. **Restore columns**: `2026_01_21_063151_add_missing_columns_to_jeepney_routes_table.php` ✅

---

**Last Updated**: January 21, 2026  
**Status**: ✅ Migration completed, API ready for Flutter integration
