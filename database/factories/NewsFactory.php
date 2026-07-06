<?php

namespace Database\Factories;

use App\Enums\NewsStatus;
use App\Models\News;
use App\Models\NewsCategory;
use App\Models\Region;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<News>
 */
class NewsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(6);

        return [
            'title' => ['tj' => $title, 'ru' => fake()->sentence(6), 'en' => fake()->sentence(6)],
            'category_id' => NewsCategory::factory(),
            'region_id' => Region::factory(),
            'excerpt' => ['tj' => fake()->sentence(12)],
            'body' => ['tj' => '<p>'.fake()->paragraph().'</p>'],
            'author' => 'Пресс-центр КҲФ',
            'status' => NewsStatus::Published,
            'published_at' => now()->subDays(fake()->numberBetween(1, 30)),
            'views' => fake()->numberBetween(0, 2000),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => NewsStatus::Draft,
            'published_at' => null,
        ]);
    }

    public function scheduled(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => NewsStatus::Scheduled,
            'published_at' => now()->addDays(fake()->numberBetween(1, 14)),
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => NewsStatus::Archived,
        ]);
    }
}
