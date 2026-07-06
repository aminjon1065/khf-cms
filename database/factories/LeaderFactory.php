<?php

namespace Database\Factories;

use App\Models\Leader;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Leader>
 */
class LeaderFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->name();

        return [
            'name' => ['tj' => $name, 'ru' => $name, 'en' => $name],
            'role' => ['tj' => 'Роль', 'ru' => 'Роль'],
            'rank' => ['tj' => 'полковник', 'ru' => 'полковник'],
            'bio' => ['tj' => fake()->sentence(10), 'ru' => fake()->sentence(10)],
            'sort' => 0,
            'active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => ['active' => false]);
    }
}
