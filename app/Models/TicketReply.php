<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketReply extends Model
{
    protected $fillable = [
        'support_ticket_id',
        'admin_id',
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
}
