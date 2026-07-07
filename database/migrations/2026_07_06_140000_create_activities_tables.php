<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('directions', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique(); // the API `id`
            $table->string('icon'); // lucide-react icon name
            $table->json('title');
            $table->json('description');
            $table->string('stat_value');
            $table->json('stat_label');
            $table->unsignedInteger('sort')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['active', 'sort']);
        });

        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->json('title');
            $table->string('period');
            $table->string('status'); // App\Enums\ProgramStatus
            $table->json('description');
            $table->unsignedInteger('sort')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['active', 'sort']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('programs');
        Schema::dropIfExists('directions');
    }
};
