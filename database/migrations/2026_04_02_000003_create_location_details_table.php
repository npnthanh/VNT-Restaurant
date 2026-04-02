<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('location_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->unique()->constrained('location')->cascadeOnDelete();
            $table->string('logo_image')->nullable();
            $table->string('cover_image')->nullable();
            $table->text('summary')->nullable();
            $table->string('intro_title')->nullable();
            $table->longText('intro_content')->nullable();
            $table->string('menu_title')->nullable();
            $table->string('menu_image')->nullable();
            $table->string('closing_title')->nullable();
            $table->longText('closing_content')->nullable();
            $table->string('address')->nullable();
            $table->string('hotline', 50)->nullable();
            $table->decimal('rating', 3, 1)->nullable();
            $table->unsignedInteger('review_count')->nullable();
            $table->string('website_url', 500)->nullable();
            $table->string('facebook_url', 500)->nullable();
            $table->string('tiktok_url', 500)->nullable();
            $table->text('booking_note')->nullable();
            $table->text('parking_note')->nullable();
            $table->text('open_note')->nullable();
            $table->timestamps();
        });

        $now = now();
        $locationIds = DB::table('location')->pluck('id');

        foreach ($locationIds as $locationId) {
            DB::table('location_details')->insert([
                'location_id' => $locationId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('location_details');
    }
};
