<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Simplifies the jeepney_routes table:
     * - Removes route_number (routes have names, not numbers)
     * - Replaces start_point/end_point with terminal (circular routes)
     * - Removes estimated_time (traffic is unpredictable in Davao)
     * - Removes fare (base fare is always ₱13.00)
     */
    public function up(): void
    {
        Schema::table('jeepney_routes', function (Blueprint $table) {
            // Add terminal column
            $table->string('terminal')->after('name')->nullable();
        });

        // Copy start_point values to terminal
        DB::table('jeepney_routes')->update([
            'terminal' => DB::raw('start_point')
        ]);

        Schema::table('jeepney_routes', function (Blueprint $table) {
            // Drop unused columns
            $table->dropIndex(['route_number']);
            $table->dropColumn(['route_number', 'start_point', 'end_point', 'estimated_time', 'fare']);
            
            // Add index on name
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jeepney_routes', function (Blueprint $table) {
            // Add back removed columns
            $table->string('route_number', 50)->after('id')->nullable();
            $table->string('start_point')->after('waypoints')->nullable();
            $table->string('end_point')->after('start_point')->nullable();
            $table->integer('estimated_time')->nullable();
            $table->decimal('fare', 8, 2)->nullable();
        });

        // Copy terminal values back to start_point and end_point
        DB::table('jeepney_routes')->update([
            'start_point' => DB::raw('terminal'),
            'end_point' => DB::raw('terminal'),
            'route_number' => DB::raw('id')
        ]);

        Schema::table('jeepney_routes', function (Blueprint $table) {
            // Make route_number unique again
            $table->unique('route_number');
            $table->index('route_number');
            
            // Drop terminal and name index
            $table->dropIndex(['name']);
            $table->dropColumn('terminal');
        });
    }
};
