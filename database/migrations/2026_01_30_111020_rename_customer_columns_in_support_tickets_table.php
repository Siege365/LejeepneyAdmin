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
            if (Schema::hasColumn('support_tickets', 'customer_name')) {
                $table->renameColumn('customer_name', 'name');
            }
            // Rename customer_email to email
            if (Schema::hasColumn('support_tickets', 'customer_email')) {
                $table->renameColumn('customer_email', 'email');
            }
        });

        // Note: Skipping ENUM modifications for PostgreSQL compatibility
        // The type and priority columns will keep their original ENUM values
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            if (Schema::hasColumn('support_tickets', 'name')) {
                $table->renameColumn('name', 'customer_name');
            }
            if (Schema::hasColumn('support_tickets', 'email')) {
                $table->renameColumn('email', 'customer_email');
            }
        });
    }
};
