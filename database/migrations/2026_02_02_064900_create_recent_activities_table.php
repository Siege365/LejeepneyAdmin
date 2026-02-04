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
        Schema::create('recent_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->enum('activity_type', ['route_calculated', 'fare_calculated', 'location_search', 'route_saved']);
            $table->string('title', 255);
            $table->text('subtitle')->nullable();
            $table->string('from_location', 255)->nullable();
            $table->string('to_location', 255)->nullable();
            $table->text('route_names')->nullable();
            $table->decimal('fare', 10, 2)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            // Indexes for performance
            $table->index('user_id');
            $table->index('created_at');
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recent_activities');
    }
};
