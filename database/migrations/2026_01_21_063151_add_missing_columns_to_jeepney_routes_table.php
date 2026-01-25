<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds back columns needed for the Flutter mobile app API:
     * - route_number: Unique identifier for routes (e.g., "01A", "02B")
     * - start_point: Starting terminal/location
     * - end_point: Ending terminal/location
     * - estimated_time: Estimated travel time in minutes
     * - fare: Base fare for the route
     */
    public function up(): void
    {
        Schema::table('jeepney_routes', function (Blueprint $table) {
            $table->string('route_number', 50)->after('id')->nullable();
            $table->string('start_point')->after('terminal')->nullable();
            $table->string('end_point')->after('start_point')->nullable();
            $table->integer('estimated_time')->after('total_distance')->nullable()->comment('Travel time in minutes');
            $table->decimal('fare', 8, 2)->after('estimated_time')->nullable()->comment('Base fare in PHP');
            
            // Add index for route_number
            $table->index('route_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jeepney_routes', function (Blueprint $table) {
            $table->dropIndex(['route_number']);
            $table->dropColumn(['route_number', 'start_point', 'end_point', 'estimated_time', 'fare']);
        });
    }
};
