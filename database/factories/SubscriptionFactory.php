<?php

namespace Database\Factories;

use App\Enums\SubmissionStatus;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subscription>
 */
class SubscriptionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'channel' => fake()->randomElement(['email', 'sms', 'telegram']),
            'region' => fake()->randomElement(['Душанбе', 'Хатлон', 'Суғд', 'ВМКБ', 'НТҶ']),
            'categories' => fake()->randomElements(['Заминҷунбӣ', 'Сел/обхезӣ', 'Обу ҳаво', 'Сӯхтор'], 2),
            'contact' => fake()->safeEmail(),
            'status' => SubmissionStatus::New,
        ];
    }

    public function status(SubmissionStatus $status): static
    {
        return $this->state(fn (array $attributes): array => ['status' => $status]);
    }
}
