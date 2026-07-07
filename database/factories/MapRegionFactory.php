<?php

namespace Database\Factories;

use App\Enums\RiskLevel;
use App\Models\MapRegion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MapRegion>
 */
class MapRegionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->city();

        return [
            'slug' => fake()->unique()->slug(2),
            'name' => ['tj' => $name, 'ru' => $name],
            'center' => ['tj' => $name, 'ru' => $name],
            'note' => ['tj' => fake()->sentence(8), 'ru' => fake()->sentence(8)],
            'risk' => fake()->randomElement(RiskLevel::cases()),
            'active_incidents' => fake()->numberBetween(0, 5),
            'stations' => fake()->numberBetween(1, 20),
            'sort' => 0,
            'active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => ['active' => false]);
    }
}
