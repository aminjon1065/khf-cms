<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('slides', function (Blueprint $table) {
            // Slide image picked from the reusable media library (falls back to
            // the legacy per-slide spatie `image` collection when null).
            $table->foreignId('image_media_asset_id')
                ->nullable()
                ->after('news_id')
                ->constrained('media_assets')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('slides', function (Blueprint $table) {
            $table->dropConstrainedForeignId('image_media_asset_id');
        });
    }
};
