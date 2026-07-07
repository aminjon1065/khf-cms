<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forum_categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique(); // the API `id`
            $table->json('title');
            $table->json('description');
            $table->unsignedInteger('topics')->default(0); // display count
            $table->unsignedInteger('posts')->default(0); // display count
            $table->string('icon'); // lucide-react name
            $table->unsignedInteger('sort')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['active', 'sort']);
        });

        Schema::create('forum_topics', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique(); // the API `id`
            $table->json('title');
            $table->string('category'); // references a forum_categories.slug
            $table->string('author');
            $table->unsignedInteger('replies')->default(0);
            $table->unsignedInteger('views')->default(0);
            $table->json('last_activity'); // display string, e.g. "2 соат пеш"
            $table->boolean('pinned')->default(false);
            $table->unsignedInteger('sort')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['active', 'sort']);
            $table->index('category');
        });

        Schema::create('forum_stats', function (Blueprint $table) {
            $table->id();
            $table->string('members')->nullable(); // string, e.g. "8 420"
            $table->string('topics')->nullable();
            $table->string('posts')->nullable();
            $table->string('online')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forum_stats');
        Schema::dropIfExists('forum_topics');
        Schema::dropIfExists('forum_categories');
    }
};
