<?php

namespace Database\Factories;

use App\Enums\SubmissionStatus;
use App\Models\Report;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Report>
 */
class ReportFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => fake()->randomElement(['Сӯхтор', 'Обхезӣ/сел', 'Заминҷунбӣ', 'Садама', 'Дигар']),
            'region' => fake()->randomElement(['Душанбе', 'Хатлон', 'Суғд', 'ВМКБ', 'НТҶ']),
            'location' => fake()->address(),
            'description' => fake()->paragraph(),
            'phone' => '+992'.fake()->numerify('#########'),
            'status' => SubmissionStatus::New,
        ];
    }

    public function status(SubmissionStatus $status): static
    {
        return $this->state(fn (array $attributes): array => ['status' => $status]);
    }
}
