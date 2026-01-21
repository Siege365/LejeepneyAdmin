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
        Schema::table('landmarks', function (Blueprint $table) {
            // Add basic landmark fields
            $table->string('name')->after('id');
            $table->decimal('latitude', 10, 8)->after('name');
            $table->decimal('longitude', 11, 8)->after('latitude');
            $table->text('description')->nullable()->after('longitude');
            
            // Add image fields
            $table->string('icon_image')->nullable()->after('description');
            $table->json('gallery_images')->nullable()->after('icon_image');
            $table->string('category', 100)->nullable()->after('gallery_images');
            $table->boolean('is_featured')->default(false)->after('category');
            
            // Add indexes
            $table->index('category');
            $table->index('is_featured');
            $table->index(['latitude', 'longitude']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('landmarks', function (Blueprint $table) {
            $table->dropIndex(['category']);
            $table->dropIndex(['is_featured']);
            $table->dropIndex(['latitude', 'longitude']);
            $table->dropColumn([
                'name', 'latitude', 'longitude', 'description',
                'icon_image', 'gallery_images', 'category', 'is_featured'
            ]);
        });
    }
};
