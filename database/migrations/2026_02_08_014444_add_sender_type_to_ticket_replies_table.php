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
        Schema::table('ticket_replies', function (Blueprint $table) {
            $table->enum('sender_type', ['admin', 'customer'])->default('admin')->after('support_ticket_id');
            $table->foreignId('user_id')->nullable()->after('admin_id')->constrained('users')->onDelete('set null');
            $table->string('sender_name')->nullable()->after('user_id');

            // Make admin columns nullable for customer replies
            $table->foreignId('admin_id')->nullable()->change();
            $table->string('admin_name')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ticket_replies', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['sender_type', 'user_id', 'sender_name']);
        });
    }
};
