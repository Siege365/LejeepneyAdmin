<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Changes activity_type from ENUM to string(50) for flexibility
     */
    public function up(): void
    {
        // Laravel 12+ handles column changes natively (no doctrine/dbal needed)
        // This works on both MySQL and PostgreSQL
        Schema::table('recent_activities', function (Blueprint $table) {
            $table->string('activity_type', 50)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // PostgreSQL note: Reverting to ENUM might fail if custom values were inserted
        // Laravel will create CHECK constraint on PostgreSQL for ENUM
        Schema::table('recent_activities', function (Blueprint $table) {
            $table->enum('activity_type', ['route_calculated', 'fare_calculated', 'location_search', 'route_saved'])->change();
        });
    }
};
