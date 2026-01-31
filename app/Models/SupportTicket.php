<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportTicket extends Model
{
    protected $fillable = [
        'user_id',
        'admin_id',
        'subject',
        'message',
        'name',
        'email',
        'type',
        'priority',
        'status',
        'is_flagged',
        'is_archived',
        'archived_at'
    ];

    protected $casts = [
        'is_flagged' => 'boolean',
        'is_archived' => 'boolean',
        'archived_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get all replies for this ticket
     */
    public function replies()
    {
        return $this->hasMany(TicketReply::class)->orderBy('created_at', 'asc');
    }

    /**
     * Get the user who submitted this ticket (mobile user)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the admin who last handled this ticket
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Scope for pending tickets
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for in-progress tickets
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in-progress');
    }

    /**
     * Scope for resolved tickets
     */
    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    /**
     * Scope for flagged (important) tickets
     */
    public function scopeFlagged($query)
    {
        return $query->where('is_flagged', true);
    }

    /**
     * Scope for active (non-archived) tickets
     */
    public function scopeActive($query)
    {
        return $query->where('is_archived', false);
    }

    /**
     * Scope for archived tickets
     */
    public function scopeArchived($query)
    {
        return $query->where('is_archived', true);
    }

    /**
     * Scope to filter by status
     */
    public function scopeFilterByStatus($query, $status)
    {
        if ($status && in_array($status, ['pending', 'in-progress', 'resolved'])) {
            return $query->where('status', $status);
        }
        return $query;
    }

    /**
     * Scope to filter by type
     */
    public function scopeFilterByType($query, $type)
    {
        if ($type && in_array($type, ['complaint', 'feedback', 'bug', 'inquiry', 'suggestion', 'report'])) {
            return $query->where('type', $type);
        }
        return $query;
    }

    /**
     * Scope to filter by priority
     */
    public function scopeFilterByPriority($query, $priority)
    {
        if ($priority && in_array($priority, ['low', 'medium', 'high'])) {
            return $query->where('priority', $priority);
        }
        return $query;
    }

    /**
     * Mark ticket as flagged/important
     */
    public function toggleFlag()
    {
        $this->is_flagged = !$this->is_flagged;
        $this->save();
        return $this;
    }

    /**
     * Archive the ticket
     */
    public function archive()
    {
        $this->is_archived = true;
        $this->archived_at = now();
        $this->save();
        return $this;
    }

    /**
     * Restore archived ticket
     */
    public function restore()
    {
        $this->is_archived = false;
        $this->archived_at = null;
        $this->save();
        return $this;
    }

    /**
     * Get status badge styles
     */
    public function getStatusStylesAttribute()
    {
        return match($this->status) {
            'resolved' => ['bg' => '#D1FAE5', 'color' => '#059669', 'icon' => 'fa-circle-check'],
            'in-progress' => ['bg' => '#DBEAFE', 'color' => '#2563EB', 'icon' => 'fa-spinner'],
            'pending' => ['bg' => '#FEF3C7', 'color' => '#D97706', 'icon' => 'fa-clock'],
            default => ['bg' => '#F3F4F6', 'color' => '#6B7280', 'icon' => 'fa-circle'],
        };
    }

    /**
     * Get priority badge styles
     */
    public function getPriorityStylesAttribute()
    {
        return match($this->priority) {
            'high' => ['bg' => '#FEE2E2', 'color' => '#DC2626', 'icon' => 'fa-circle-exclamation'],
            'medium' => ['bg' => '#FEF3C7', 'color' => '#D97706', 'icon' => 'fa-circle-pause'],
            'low' => ['bg' => '#DBEAFE', 'color' => '#2563EB', 'icon' => 'fa-circle-info'],
            default => ['bg' => '#F3F4F6', 'color' => '#6B7280', 'icon' => 'fa-circle'],
        };
    }

    /**
     * Get type badge styles
     */
    public function getTypeStylesAttribute()
    {
        return match($this->type) {
            'complaint' => ['bg' => '#FEE2E2', 'color' => '#DC2626', 'icon' => 'fa-exclamation-circle'],
            'feedback' => ['bg' => '#E0E7FF', 'color' => '#6366F1', 'icon' => 'fa-comment'],
            'bug' => ['bg' => '#FECACA', 'color' => '#B91C1C', 'icon' => 'fa-bug'],
            'inquiry' => ['bg' => '#DBEAFE', 'color' => '#2563EB', 'icon' => 'fa-circle-question'],
            'suggestion' => ['bg' => '#D1FAE5', 'color' => '#059669', 'icon' => 'fa-lightbulb'],
            'report' => ['bg' => '#FEF3C7', 'color' => '#D97706', 'icon' => 'fa-flag'],
            default => ['bg' => '#F3F4F6', 'color' => '#6B7280', 'icon' => 'fa-circle'],
        };
    }
}
