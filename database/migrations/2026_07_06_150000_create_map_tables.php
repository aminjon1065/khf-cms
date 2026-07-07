<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('map_regions', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique(); // the API `id`
            $table->json('name');
            $table->json('center');
            $table->json('note');
            $table->string('risk'); // App\Enums\RiskLevel
            $table->unsignedInteger('active_incidents')->default(0);
            $table->unsignedInteger('stations')->default(0);
            $table->unsignedInteger('sort')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['active', 'sort']);
        });

        Schema::create('map_settings', function (Blueprint $table) {
            $table->id();
            $table->string('monitoring')->nullable(); // global stats string, e.g. "320+"
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('map_settings');
        Schema::dropIfExists('map_regions');
    }
};
