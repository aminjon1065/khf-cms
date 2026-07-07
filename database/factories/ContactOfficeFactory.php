<?php

namespace Database\Factories;

use App\Models\ContactOffice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContactOffice>
 */
class ContactOfficeFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $region = fake()->city();
        $hours = 'Душанбе–Ҷумъа, 8:00–17:00';

        return [
            'region' => ['tj' => $region, 'ru' => $region],
            'address' => ['tj' => fake()->streetAddress(), 'ru' => fake()->streetAddress()],
            'hours' => ['tj' => $hours, 'ru' => $hours],
            'phone' => fake()->phoneNumber(),
            'email' => fake()->safeEmail(),
            'is_head' => false,
            'sort' => 0,
            'active' => true,
        ];
    }

    public function head(): static
    {
        return $this->state(fn (array $attributes): array => ['is_head' => true]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => ['active' => false]);
    }
}
