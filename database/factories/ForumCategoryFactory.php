<?php

namespace Database\Factories;

use App\Models\ForumCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ForumCategory>
 */
class ForumCategoryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->words(2, true);

        return [
            'slug' => fake()->unique()->slug(2),
            'title' => ['tj' => $title, 'ru' => $title],
            'description' => ['tj' => fake()->sentence(8), 'ru' => fake()->sentence(8)],
            'topics' => fake()->numberBetween(10, 300),
            'posts' => fake()->numberBetween(100, 2000),
            'icon' => fake()->randomElement(['MessagesSquare', 'ShieldAlert', 'HeartHandshake', 'HelpCircle']),
            'sort' => 0,
            'active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => ['active' => false]);
    }
}
