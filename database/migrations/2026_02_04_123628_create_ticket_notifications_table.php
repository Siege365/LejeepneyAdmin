<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates ticket_notifications table for real-time notification tracking
     */
    public function up(): void
    {
        Schema::create('ticket_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('support_tickets')->cascadeOnDelete();
            $table->string('user_email')->index(); // Email of the user to notify
            $table->enum('event_type', ['created', 'replied', 'status_changed', 'resolved', 'admin_message'])->index();
            $table->string('title'); // Notification title
            $table->text('message'); // Notification message
            $table->json('metadata')->nullable(); // Additional data (old_status, new_status, admin_name, etc.)
            $table->boolean('is_read')->default(false)->index();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            // Indexes for efficient querying
            $table->index(['user_email', 'is_read', 'created_at']);
            $table->index(['ticket_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_notifications');
    }
};
