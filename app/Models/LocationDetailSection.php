<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LocationDetailSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_detail_id',
        'title',
        'content',
        'image',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function detail(): BelongsTo
    {
        return $this->belongsTo(LocationDetail::class, 'location_detail_id', 'id');
    }
}
