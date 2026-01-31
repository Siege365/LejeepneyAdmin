<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds additional features to support_tickets:
     * - user_id: Links to mobile app user who submitted ticket
     * - is_flagged: Mark ticket as important
     * - is_archived: Soft archive without deleting
     * - archived_at: Timestamp when archived
     * - admin_id: Last admin who handled the ticket
     */
    public function up(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            // Link to mobile user (nullable for guest submissions)
            $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
            
            // Admin who last handled this ticket
            $table->foreignId('admin_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            
            // Feature flags
            $table->boolean('is_flagged')->default(false)->after('status');
            $table->boolean('is_archived')->default(false)->after('is_flagged');
            $table->timestamp('archived_at')->nullable()->after('is_archived');
            
            // Indexes for filtering
            $table->index('status');
            $table->index('type');
            $table->index('priority');
            $table->index('is_flagged');
            $table->index('is_archived');
        });

        // Add admin_id to ticket_replies
        Schema::table('ticket_replies', function (Blueprint $table) {
            $table->foreignId('admin_id')->nullable()->after('support_ticket_id')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ticket_replies', function (Blueprint $table) {
            $table->dropForeign(['admin_id']);
            $table->dropColumn('admin_id');
        });

        Schema::table('support_tickets', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['admin_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['type']);
            $table->dropIndex(['priority']);
            $table->dropIndex(['is_flagged']);
            $table->dropIndex(['is_archived']);
            $table->dropColumn(['user_id', 'admin_id', 'is_flagged', 'is_archived', 'archived_at']);
        });
    }
};
