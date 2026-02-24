<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('inventory_check', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code', 20)->nullable()->unique();
            $table->unsignedBigInteger('staff_id')->nullable();
            $table->dateTime('check_time')->nullable();
            $table->dateTime('balance_time')->nullable();
            $table->enum('status', ['draft', 'completed'])->default('draft');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('inventory_check');
    }
};
