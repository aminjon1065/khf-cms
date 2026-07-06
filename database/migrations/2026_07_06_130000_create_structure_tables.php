<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leaders', function (Blueprint $table) {
            $table->id();
            $table->json('name');
            $table->json('role');
            $table->json('rank')->nullable();
            $table->json('bio');
            $table->unsignedInteger('sort')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['active', 'sort']);
        });

        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->json('title');
            $table->json('description');
            $table->json('head')->nullable();
            $table->unsignedInteger('sort')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['active', 'sort']);
        });

        Schema::create('regional_offices', function (Blueprint $table) {
            $table->id();
            $table->json('region');
            $table->json('head');
            $table->string('phone')->nullable();
            $table->json('address');
            $table->unsignedInteger('sort')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['active', 'sort']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('regional_offices');
        Schema::dropIfExists('departments');
        Schema::dropIfExists('leaders');
    }
};
