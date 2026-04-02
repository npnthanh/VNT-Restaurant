<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('location_detail_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_detail_id')->constrained('location_details')->cascadeOnDelete();
            $table->string('title');
            $table->longText('content')->nullable();
            $table->string('image')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('location_detail_sections');
    }
};
