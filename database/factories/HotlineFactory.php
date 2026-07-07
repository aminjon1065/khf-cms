<?php

namespace Database\Factories;

use App\Models\Hotline;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Hotline>
 */
class HotlineFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $label = fake()->company();

        return [
            'number' => fake()->numerify('(992 37) ###-##-##'),
            'label' => ['tj' => $label, 'ru' => $label],
            'note' => ['tj' => fake()->sentence(6), 'ru' => fake()->sentence(6)],
            'is_primary' => false,
            'sort' => 0,
            'active' => true,
        ];
    }

    public function primary(): static
    {
        return $this->state(fn (array $attributes): array => ['is_primary' => true]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => ['active' => false]);
    }
}
