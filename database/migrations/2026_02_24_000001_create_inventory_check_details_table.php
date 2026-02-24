<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('inventory_check_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('inventory_check_id');
            $table->unsignedBigInteger('ingredient_id');
            $table->decimal('stock_qty', 12, 2)->default(0);
            $table->decimal('actual_qty', 12, 2)->default(0);
            $table->decimal('diff_qty', 12, 2)->default(0);
            $table->decimal('price', 12, 2)->default(0);
            $table->timestamps();

            $table->index('inventory_check_id');
            $table->index('ingredient_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('inventory_check_details');
    }
};
