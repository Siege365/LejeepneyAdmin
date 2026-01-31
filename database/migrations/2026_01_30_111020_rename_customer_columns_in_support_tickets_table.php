<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            // Rename customer_name to name
            $table->renameColumn('customer_name', 'name');
            // Rename customer_email to email
            $table->renameColumn('customer_email', 'email');
        });

        // Update type enum to include new values (need to modify enum)
        DB::statement("ALTER TABLE support_tickets MODIFY COLUMN type ENUM('general', 'technical', 'billing', 'feedback', 'other', 'complaint', 'bug', 'inquiry', 'suggestion', 'report') DEFAULT 'general'");
        
        // Update priority enum to include 'urgent'
        DB::statement("ALTER TABLE support_tickets MODIFY COLUMN priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->renameColumn('name', 'customer_name');
            $table->renameColumn('email', 'customer_email');
        });

        DB::statement("ALTER TABLE support_tickets MODIFY COLUMN type ENUM('complaint', 'feedback', 'bug', 'inquiry', 'suggestion', 'report') DEFAULT 'inquiry'");
        DB::statement("ALTER TABLE support_tickets MODIFY COLUMN priority ENUM('low', 'medium', 'high') DEFAULT 'medium'");
    }
};
