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
        Schema::create('home_settings', function (Blueprint $table) {
            $table->id();
            // Цитата Президента (ToR §6.9)
            $table->string('president_name')->nullable();
            $table->json('president_role')->nullable();   // translatable
            $table->json('president_quote')->nullable();  // translatable
            $table->string('president_href')->nullable();
            // Статистика сайта (строки — форматирование на стороне CMS)
            $table->string('stats_today')->nullable();
            $table->string('stats_month')->nullable();
            $table->string('stats_rescued')->nullable();
            $table->string('stats_reaction')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('home_settings');
    }
};
