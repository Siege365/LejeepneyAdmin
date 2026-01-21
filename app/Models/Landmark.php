<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Landmark extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'latitude',
        'longitude',
        'description',
        'icon_image',
        'gallery_images',
        'category',
        'is_featured'
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'gallery_images' => 'array',
        'is_featured' => 'boolean'
    ];

    /**
     * Available landmark categories
     */
    const CATEGORIES = [
        'city_center' => 'Downtown / City Center',
        'mall' => 'Malls & Commercial',
        'school' => 'Schools & Institutions',
        'hospital' => 'Hospitals',
        'transport' => 'Transport & Terminals',
        'other' => 'Other'
    ];

    /**
     * Get the category label
     */
    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? 'Other';
    }
}
