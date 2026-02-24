<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryCheckDetail extends Model
{
    use HasFactory;

    protected $table = 'inventory_check_details';

    protected $fillable = [
        'inventory_check_id',
        'ingredient_id',
        'stock_qty',
        'actual_qty',
        'diff_qty',
        'price',
    ];

    public function inventoryCheck()
    {
        return $this->belongsTo(InventoryCheck::class, 'inventory_check_id');
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class, 'ingredient_id');
    }
}
