<?php

use App\Services\ApiTokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function apiToken(): string
{
    return app(ApiTokenService::class)->generateFrontendToken();
}

test('api health defaults to tajik locale without prefix', function () {
    $this->withToken(apiToken())
        ->getJson('/api/v1/health')
        ->assertOk()
        ->assertJsonPath('data.locale', 'tj');
});

test('api health maps tg prefix to tajik locale', function () {
    $this->withToken(apiToken())
        ->getJson('/api/v1/tg/health')
        ->assertOk()
        ->assertJsonPath('data.locale', 'tj');
});

test('api health accepts ru and en locale prefixes', function () {
    $token = apiToken();

    $this->withToken($token)
        ->getJson('/api/v1/ru/health')
        ->assertOk()
        ->assertJsonPath('data.locale', 'ru');

    $this->withToken($token)
        ->getJson('/api/v1/en/health')
        ->assertOk()
        ->assertJsonPath('data.locale', 'en');
});

test('api auth failures return json 401 even without an accept header', function () {
    // A guest without the Accept header must still get 401 JSON, not a 500 from
    // Laravel redirecting to a non-existent `login` route (ForceJsonResponse).
    $this->get('/api/v1/news')
        ->assertUnauthorized()
        ->assertJson(['message' => 'Unauthenticated.']);
});

test('api returns json 404 for unknown routes', function () {
    $this->withToken(apiToken())
        ->getJson('/api/v1/unknown-endpoint')
        ->assertNotFound()
        ->assertJson(['message' => 'Not found']);
});

test('api returns json 422 for validation errors', function () {
    $this->postJson('/api/v1/validation-probe', [])
        ->assertUnprocessable()
        ->assertJsonStructure(['message', 'errors' => ['name']]);
});

test('api returns json 429 when throttled', function () {
    $this->getJson('/api/v1/throttle-probe')->assertOk();
    $this->getJson('/api/v1/throttle-probe')->assertOk();
    $this->getJson('/api/v1/throttle-probe')->assertStatus(429);
});
