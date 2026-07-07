<?php

use App\Enums\SubmissionStatus;
use App\Models\ContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * @return array<string, string>
 */
function validContact(array $overrides = []): array
{
    return array_merge([
        'name' => 'Гулнора Каримова',
        'email' => 'gulnora@example.tj',
        'subject' => 'Вопрос о подписке',
        'message' => 'Как подписаться на оповещения по Хатлону?',
    ], $overrides);
}

test('a valid contact message is stored and acknowledged without a data wrapper', function () {
    $response = $this->postJson('/api/v1/contact', validContact());

    $response->assertOk()
        ->assertExactJson(['ok' => true]);

    $message = ContactMessage::query()->firstOrFail();

    expect($message->status)->toBe(SubmissionStatus::New)
        ->and($message->email)->toBe('gulnora@example.tj')
        ->and($message->subject)->toBe('Вопрос о подписке');
});

test('the contact form requires all fields', function () {
    $this->postJson('/api/v1/contact', [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'email', 'subject', 'message']);

    expect(ContactMessage::query()->count())->toBe(0);
});

test('an invalid email is rejected', function () {
    $this->postJson('/api/v1/contact', validContact(['email' => 'not-an-email']))
        ->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('a filled honeypot is rejected', function () {
    $this->postJson('/api/v1/contact', validContact(['website' => 'http://spam.example']))
        ->assertStatus(422)
        ->assertJsonValidationErrors(['website']);

    expect(ContactMessage::query()->count())->toBe(0);
});
