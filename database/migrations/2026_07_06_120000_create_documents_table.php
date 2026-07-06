<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->json('title'); // translatable {tj, ru, en}
            $table->string('category'); // App\Enums\DocumentCategory
            $table->string('number')->nullable();
            $table->date('document_date')->nullable();
            $table->string('type')->nullable(); // App\Enums\DocType, derived from the file
            $table->string('size')->nullable(); // human-readable, e.g. "420 КБ"
            $table->string('file_path')->nullable();
            $table->unsignedInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'category', 'document_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
