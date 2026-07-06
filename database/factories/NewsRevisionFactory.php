<?php

namespace Database\Factories;

use App\Models\News;
use App\Models\NewsRevision;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NewsRevision>
 */
class NewsRevisionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'news_id' => News::factory(),
            'user_id' => null,
            'data' => [
                'slug' => fake()->slug(),
                'category_id' => null,
                'region_id' => null,
                'author' => 'Пресс-центр КҲФ',
                'status' => 'draft',
                'published_at' => null,
                'title' => ['tj' => fake()->sentence()],
                'excerpt' => ['tj' => fake()->sentence()],
                'body' => ['tj' => '<p>'.fake()->paragraph().'</p>'],
                'seo_title' => [],
                'seo_description' => [],
            ],
        ];
    }
}
