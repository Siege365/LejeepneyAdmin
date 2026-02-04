<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketNotification extends Model
{
    protected $fillable = [
        'ticket_id',
        'user_email',
        'event_type',
        'title',
        'message',
        'metadata',
        'is_read',
        'read_at'
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relationship: Notification belongs to a support ticket
     */
    public function ticket()
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }

    /**
     * Scope: Get unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope: Get notifications for specific email
     */
    public function scopeForEmail($query, $email)
    {
        return $query->where('user_email', $email);
    }

    /**
     * Scope: Get recent notifications
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Mark notification as read
     */
    public function markAsRead()
    {
        $this->update([
            'is_read' => true,
            'read_at' => now()
        ]);
    }

    /**
     * Create a notification for ticket event
     */
    public static function createNotification($ticketId, $userEmail, $eventType, $title, $message, $metadata = [])
    {
        return self::create([
            'ticket_id' => $ticketId,
            'user_email' => $userEmail,
            'event_type' => $eventType,
            'title' => $title,
            'message' => $message,
            'metadata' => $metadata
        ]);
    }
}
