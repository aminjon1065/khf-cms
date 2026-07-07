<?php

namespace Database\Factories;

use App\Models\Direction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Direction>
 */
class DirectionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->words(3, true);

        return [
            'slug' => fake()->unique()->slug(2),
            'icon' => fake()->randomElement(['LifeBuoy', 'ShieldAlert', 'Users', 'Flame', 'CloudRain', 'GraduationCap']),
            'title' => ['tj' => $title, 'ru' => $title],
            'description' => ['tj' => fake()->sentence(10), 'ru' => fake()->sentence(10)],
            'stat_value' => (string) fake()->numberBetween(10, 9999),
            'stat_label' => ['tj' => 'нишондиҳанда', 'ru' => 'показатель'],
            'sort' => 0,
            'active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => ['active' => false]);
    }
}
