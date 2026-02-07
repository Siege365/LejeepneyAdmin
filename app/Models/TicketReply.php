<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketReply extends Model
{
    protected $fillable = [
        'support_ticket_id',
        'sender_type',
        'admin_id',
        'user_id',
        'sender_name',
        'message',
        'admin_name',
        'email_sent'
    ];

    protected $casts = [
        'email_sent' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Check if this reply is from a customer
     */
    public function isCustomer(): bool
    {
        return $this->sender_type === 'customer';
    }

    /**
     * Check if this reply is from an admin
     */
    public function isAdmin(): bool
    {
        return $this->sender_type === 'admin';
    }

    /**
     * Get the display name of the sender
     */
    public function getSenderDisplayNameAttribute(): string
    {
        if ($this->sender_type === 'customer') {
            return $this->sender_name ?? $this->ticket?->name ?? 'Customer';
        }
        return $this->admin_name ?? 'Admin Support';
    }

    /**
     * Get the ticket this reply belongs to
     */
    public function ticket()
    {
        return $this->belongsTo(SupportTicket::class, 'support_ticket_id');
    }

    /**
     * Get the admin who wrote this reply
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Get the user who wrote this reply
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
