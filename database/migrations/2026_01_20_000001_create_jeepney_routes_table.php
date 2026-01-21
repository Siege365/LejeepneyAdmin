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
        Schema::create('jeepney_routes', function (Blueprint $table) {
            $table->id();
            $table->string('route_number', 50)->unique();
            $table->string('name');
            $table->json('path');
            $table->json('waypoints')->nullable();
            $table->string('start_point');
            $table->string('end_point');
            $table->decimal('total_distance', 8, 2)->nullable();
            $table->enum('status', ['available', 'unavailable'])->default('available');
            $table->string('color', 7)->default('#EBAF3E');
            $table->text('description')->nullable();
            $table->integer('estimated_time')->nullable();
            $table->decimal('fare', 8, 2)->nullable();
            $table->timestamps();

            $table->index('route_number');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jeepney_routes');
    }
};
