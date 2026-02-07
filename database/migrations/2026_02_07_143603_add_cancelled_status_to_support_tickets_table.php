<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add 'cancelled' to the status enum
        DB::statement("ALTER TABLE support_tickets MODIFY COLUMN status ENUM('pending', 'in-progress', 'resolved', 'cancelled') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'cancelled' from the status enum
        DB::statement("ALTER TABLE support_tickets MODIFY COLUMN status ENUM('pending', 'in-progress', 'resolved') DEFAULT 'pending'");
    }
};
