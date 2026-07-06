<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->json('title');   // translatable {tj, ru, en}
            $table->string('slug')->unique();
            $table->foreignId('category_id')->constrained('news_categories')->restrictOnDelete();
            $table->foreignId('region_id')->nullable()->constrained('regions')->nullOnDelete();
            $table->json('excerpt');  // translatable
            $table->json('body');     // translatable (sanitized Tiptap HTML)
            $table->string('author')->default('Пресс-центр КҲФ');
            $table->string('status')->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->unsignedBigInteger('views')->default(0);
            $table->json('seo_title')->nullable();        // translatable
            $table->json('seo_description')->nullable();   // translatable
            $table->string('og_image')->nullable();
            $table->timestamps();

            $table->index(['status', 'published_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news');
    }
};
