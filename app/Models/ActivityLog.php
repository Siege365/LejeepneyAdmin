<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ActivityLog extends Model
{
    protected $fillable = [
        'action',
        'model_type',
        'model_id',
        'model_name',
        'user_id',
        'user_name',
        'description',
        'changes',
        'ip_address',
    ];

    protected $casts = [
        'changes' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Log an activity
     */
    public static function log($action, $modelType, $modelId, $modelName, $description = null, $changes = null)
    {
        return static::create([
            'action' => $action,
            'model_type' => $modelType,
            'model_id' => $modelId,
            'model_name' => $modelName,
            'user_id' => auth()->id(),
            'user_name' => auth()->user()?->name ?? 'System',
            'description' => $description,
            'changes' => $changes,
            'ip_address' => request()->ip(),
        ]);
    }

    /**
     * Get activity icon based on action
     */
    public function getIconAttribute()
    {
        // Check if it's a customer service ticket activity
        if ($this->model_type === 'SupportTicket' || $this->model_type === 'App\\Models\\SupportTicket') {
            return 'fa-ticket';
        }

        return match($this->action) {
            'created' => 'fa-plus-circle',
            'updated' => 'fa-edit',
            'deleted' => 'fa-trash',
            default => 'fa-circle',
        };
    }

    /**
     * Get activity color based on action
     */
    public function getColorAttribute()
    {
        return match($this->action) {
            'created' => 'success',
            'updated' => 'info',
            'deleted' => 'danger',
            'ticket_reply' => 'primary',
            'ticket_status_change' => 'warning',
            'ticket_flag_toggle' => 'secondary',
            default => 'secondary',
        };
    }
}
