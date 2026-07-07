<?php

namespace Database\Factories;

use App\Enums\ProgramStatus;
use App\Models\Program;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Program>
 */
class ProgramFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->words(3, true);

        return [
            'title' => ['tj' => $title, 'ru' => $title],
            'period' => '2024–2028',
            'status' => fake()->randomElement(ProgramStatus::cases()),
            'description' => ['tj' => fake()->sentence(10), 'ru' => fake()->sentence(10)],
            'sort' => 0,
            'active' => true,
        ];
    }

    public function status(ProgramStatus $status): static
    {
        return $this->state(fn (array $attributes): array => ['status' => $status]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => ['active' => false]);
    }
}
