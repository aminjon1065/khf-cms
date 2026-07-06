<?php

namespace Database\Factories;

use App\Models\RegionalOffice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RegionalOffice>
 */
class RegionalOfficeFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $region = fake()->city();

        return [
            'region' => ['tj' => $region, 'ru' => $region],
            'head' => ['tj' => 'полковник '.fake()->lastName(), 'ru' => 'полковник '.fake()->lastName()],
            'phone' => fake()->phoneNumber(),
            'address' => ['tj' => fake()->streetAddress(), 'ru' => fake()->streetAddress()],
            'sort' => 0,
            'active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => ['active' => false]);
    }
}
