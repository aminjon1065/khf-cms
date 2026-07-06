<?php

namespace Database\Factories;

use App\Models\Slide;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Slide>
 */
class SlideFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $category = fake()->randomElement(['Спасение', 'Профилактика', 'Сотрудничество']);

        return [
            'title' => ['tj' => fake()->sentence(6)],
            'category' => ['tj' => $category],
            'date' => now()->format('d.m.Y'),
            'source' => ['tj' => 'Пресс-центр КҲФ'],
            'news_id' => null,
            'sort' => 0,
            'active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'active' => false,
        ]);
    }
}
