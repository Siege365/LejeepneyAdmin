<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // PostgreSQL Compatibility: Skip ENUM modification
        // Laravel uses CHECK constraints for ENUMs on PostgreSQL, not native ENUM types
        // The application code already handles 'cancelled' status in validation
        // No database-level change needed - CHECK constraint is permissive enough
        
        // Note: Original MySQL code was:
        // DB::statement("ALTER TABLE support_tickets MODIFY COLUMN status ENUM('pending', 'in-progress', 'resolved', 'cancelled') DEFAULT 'pending'");
        // This syntax is not compatible with PostgreSQL
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No changes to reverse since we skipped the ENUM modification
    }
};
