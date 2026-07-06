<?php

namespace Database\Factories;

use App\Enums\CategoryColor;
use App\Models\NewsCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NewsCategory>
 */
class NewsCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $label = fake()->unique()->words(2, true);

        return [
            'label' => ['tj' => $label, 'ru' => $label, 'en' => $label],
            'color' => fake()->randomElement(CategoryColor::cases()),
            'sort' => 0,
            'active' => true,
        ];
    }
}
