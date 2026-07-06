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
        Schema::create('slides', function (Blueprint $table) {
            $table->id();
            $table->json('title');    // translatable {tj, ru, en}
            $table->json('category');  // translatable badge label
            $table->string('date')->nullable();  // manual DD.MM.YYYY string (ToR §6.2)
            $table->json('source')->nullable();   // translatable
            $table->foreignId('news_id')->nullable()->constrained('news')->nullOnDelete();
            $table->unsignedInteger('sort')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['active', 'sort']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('slides');
    }
};
