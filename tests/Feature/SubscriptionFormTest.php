<?php

use App\Enums\SubmissionStatus;
use App\Models\Subscription;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * @return array<string, mixed>
 */
function validSubscription(array $overrides = []): array
{
    return array_merge([
        'channel' => 'email',
        'region' => 'Хатлон',
        'categories' => ['Заминҷунбӣ', 'Сел/обхезӣ'],
        'contact' => 'user@example.tj',
    ], $overrides);
}

test('a valid subscription is stored and acknowledged with a reference', function () {
    $response = $this->postJson('/api/v1/subscriptions', validSubscription());

    $response->assertOk()
        ->assertJson(['ok' => true])
        ->assertJsonStructure(['ok', 'reference'])
        ->assertJsonMissingPath('data');

    expect($response->json('reference'))->toMatch('/^SUB-\d{6}$/');

    $subscription = Subscription::query()->firstOrFail();

    expect($subscription->status)->toBe(SubmissionStatus::New)
        ->and($subscription->categories)->toBe(['Заминҷунбӣ', 'Сел/обхезӣ'])
        ->and($subscription->reference)->toBe($response->json('reference'));
});

test('an sms contact is normalized as a phone number', function () {
    $this->postJson('/api/v1/subscriptions', validSubscription([
        'channel' => 'sms',
        'contact' => '+992 37 221-12-12',
    ]))->assertOk();

    expect(Subscription::query()->firstOrFail()->contact)->toBe('+992372211212');
});

test('the subscription form requires all fields', function () {
    $this->postJson('/api/v1/subscriptions', [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['channel', 'region', 'categories', 'contact']);

    expect(Subscription::query()->count())->toBe(0);
});

test('an unknown channel is rejected', function () {
    $this->postJson('/api/v1/subscriptions', validSubscription(['channel' => 'carrier-pigeon']))
        ->assertStatus(422)
        ->assertJsonValidationErrors(['channel']);
});

test('at least one category is required', function () {
    $this->postJson('/api/v1/subscriptions', validSubscription(['categories' => []]))
        ->assertStatus(422)
        ->assertJsonValidationErrors(['categories']);
});

test('an email channel requires a valid email contact', function () {
    $this->postJson('/api/v1/subscriptions', validSubscription([
        'channel' => 'email',
        'contact' => 'not-an-email',
    ]))
        ->assertStatus(422)
        ->assertJsonValidationErrors(['contact']);
});

test('a filled honeypot is rejected', function () {
    $this->postJson('/api/v1/subscriptions', validSubscription(['website' => 'http://spam.example']))
        ->assertStatus(422)
        ->assertJsonValidationErrors(['website']);

    expect(Subscription::query()->count())->toBe(0);
});
