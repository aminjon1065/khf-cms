<?php

namespace Database\Factories;

use App\Enums\DocType;
use App\Enums\DocumentCategory;
use App\Models\Document;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Document>
 */
class DocumentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(5);
        $type = fake()->randomElement(DocType::cases());

        return [
            'title' => ['tj' => $title, 'ru' => $title, 'en' => $title],
            'category' => fake()->randomElement(DocumentCategory::cases()),
            'number' => '№ '.fake()->numberBetween(1, 999),
            'document_date' => fake()->dateTimeBetween('-10 years', 'now')->format('Y-m-d'),
            'type' => $type,
            'size' => fake()->numberBetween(50, 900).' КБ',
            'file_path' => null,
            'sort' => 0,
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }

    public function category(DocumentCategory $category): static
    {
        return $this->state(fn (array $attributes): array => [
            'category' => $category,
        ]);
    }
}
