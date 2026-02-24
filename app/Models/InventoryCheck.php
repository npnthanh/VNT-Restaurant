<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryCheck extends Model
{
    use HasFactory;

    protected $table = 'inventory_check';

    protected $fillable = [
        'code',
        'staff_id',
        'check_time',
        'balance_time',
        'status',
        'note',
    ];

    protected $casts = [
        'check_time' => 'datetime',
        'balance_time' => 'datetime',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    public function details()
    {
        return $this->hasMany(InventoryCheckDetail::class, 'inventory_check_id');
    }
}
