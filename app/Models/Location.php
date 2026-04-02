<?php

namespace App\Models;

use App\Models\Region;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Location extends Model
{
    use HasFactory;

    protected $table = 'location';
    public $timestamps = false;

    protected $fillable = [
        'region_id',
        'code',
        'thumbnail',
        'status',
        'name',
        'slug',
        'capacity',
        'area',
        'floors',
        'time_start',
        'time_end',
        'map_url',
        'created_at',
    ];

    public function region()
    {
        return $this->belongsTo(Region::class, 'region_id', 'id');
    }

    public function detail(): HasOne
    {
        return $this->hasOne(LocationDetail::class, 'location_id', 'id');
    }
}
