<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('location', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name');
        });

        $existingSlugs = [];

        DB::table('location')
            ->select('id', 'name', 'code')
            ->orderBy('id')
            ->get()
            ->each(function ($location) use (&$existingSlugs) {
                $baseSlug = Str::slug($location->name ?: $location->code ?: 'co-so');
                if ($baseSlug === '') {
                    $baseSlug = 'co-so';
                }

                $slug = $baseSlug;
                $suffix = 2;

                while (in_array($slug, $existingSlugs, true)) {
                    $slug = $baseSlug . '-' . $suffix;
                    $suffix++;
                }

                DB::table('location')
                    ->where('id', $location->id)
                    ->update(['slug' => $slug]);

                $existingSlugs[] = $slug;
            });

        Schema::table('location', function (Blueprint $table) {
            $table->unique('slug');
        });
    }

    public function down(): void
    {
        Schema::table('location', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
