<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebsiteSetting extends Model
{
    public $incrementing = false;

    protected $fillable = ['id', 'data'];

    protected $casts = ['data' => 'array'];
}
