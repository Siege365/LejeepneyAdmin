<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('recent_activities', function (Blueprint $table) {
            $table->string('activity_type', 50)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recent_activities', function (Blueprint $table) {
            $table->enum('activity_type', ['route_calculated', 'fare_calculated', 'location_search', 'route_saved'])->change();
        });
    }
};
