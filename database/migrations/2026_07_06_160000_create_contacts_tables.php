<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotlines', function (Blueprint $table) {
            $table->id();
            $table->string('number');
            $table->json('label');
            $table->json('note');
            $table->boolean('is_primary')->default(false); // API `primary`
            $table->unsignedInteger('sort')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['active', 'sort']);
        });

        Schema::create('contact_offices', function (Blueprint $table) {
            $table->id();
            $table->json('region');
            $table->json('address');
            $table->json('hours');
            $table->string('phone');
            $table->string('email');
            $table->boolean('is_head')->default(false); // the single central office (headOffice)
            $table->unsignedInteger('sort')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['active', 'sort']);
            $table->index('is_head');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_offices');
        Schema::dropIfExists('hotlines');
    }
};
