<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LocationDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_id',
        'logo_image',
        'cover_image',
        'summary',
        'intro_title',
        'intro_content',
        'menu_title',
        'menu_image',
        'closing_title',
        'closing_content',
        'address',
        'hotline',
        'rating',
        'review_count',
        'website_url',
        'facebook_url',
        'tiktok_url',
        'booking_note',
        'parking_note',
        'open_note',
    ];

    protected $casts = [
        'rating' => 'decimal:1',
        'review_count' => 'integer',
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id', 'id');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(LocationDetailSection::class, 'location_detail_id', 'id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }
}
