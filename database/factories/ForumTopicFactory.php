<?php

namespace Database\Factories;

use App\Models\ForumTopic;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ForumTopic>
 */
class ForumTopicFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(6);
        $activity = fake()->randomElement(['2 соат пеш', 'имрӯз, 09:14', 'дирӯз', '2 рӯз пеш']);

        return [
            'slug' => fake()->unique()->slug(2),
            'title' => ['tj' => $title, 'ru' => $title],
            'category' => fake()->slug(1),
            'author' => fake()->userName(),
            'replies' => fake()->numberBetween(0, 60),
            'views' => fake()->numberBetween(50, 2000),
            'last_activity' => ['tj' => $activity, 'ru' => $activity],
            'pinned' => false,
            'sort' => 0,
            'active' => true,
        ];
    }

    public function pinned(): static
    {
        return $this->state(fn (array $attributes): array => ['pinned' => true]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => ['active' => false]);
    }
}
