<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecentActivity extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'activity_type',
        'title',
        'subtitle',
        'from_location',
        'to_location',
        'route_names',
        'fare',
        'metadata'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'metadata' => 'array',
        'fare' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the user that owns the activity.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include activities for a specific user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to only include guest activities.
     */
    public function scopeGuest($query)
    {
        return $query->whereNull('user_id');
    }

    /**
     * Scope a query to filter by activity type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('activity_type', $type);
    }

    /**
     * Scope a query to get recent activities ordered by latest first.
     */
    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}
