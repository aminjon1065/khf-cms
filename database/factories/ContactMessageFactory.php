<?php

namespace Database\Factories;

use App\Enums\SubmissionStatus;
use App\Models\ContactMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContactMessage>
 */
class ContactMessageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'subject' => fake()->sentence(4),
            'message' => fake()->paragraph(),
            'status' => SubmissionStatus::New,
        ];
    }

    public function status(SubmissionStatus $status): static
    {
        return $this->state(fn (array $attributes): array => ['status' => $status]);
    }
}
