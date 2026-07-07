<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('news', function (Blueprint $table) {
            // Cover picked from the reusable media library (falls back to the
            // legacy per-post spatie `cover` collection when null).
            $table->foreignId('cover_media_asset_id')
                ->nullable()
                ->after('region_id')
                ->constrained('media_assets')
                ->nullOnDelete();
        });

        // Ordered gallery of reusable library images (CMS-only; not in the API).
        Schema::create('news_gallery', function (Blueprint $table) {
            $table->id();
            $table->foreignId('news_id')->constrained('news')->cascadeOnDelete();
            $table->foreignId('media_asset_id')->constrained('media_assets')->cascadeOnDelete();
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();

            $table->unique(['news_id', 'media_asset_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_gallery');

        Schema::table('news', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cover_media_asset_id');
        });
    }
};
