<?php

use App\Models\User;
use App\Services\ApiTokenService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

function createAdminWithTwoFactorForApi(): User
{
    $user = User::factory()->create();
    $user->assignRole('admin');
    $user->saveAppAuthenticationSecret('test-secret');

    return $user;
}

test('generate api token command creates frontend bearer token', function () {
    $this->artisan('khf:generate-api-token')
        ->assertSuccessful();

    expect(app(ApiTokenService::class)->hasFrontendToken())->toBeTrue();
});

test('get api routes require sanctum bearer token', function () {
    $this->getJson('/api/v1/health')
        ->assertUnauthorized();
});

test('get api routes accept valid sanctum bearer token', function () {
    $token = app(ApiTokenService::class)->generateFrontendToken();

    $this->withToken($token)
        ->getJson('/api/v1/health')
        ->assertOk()
        ->assertJson(['data' => ['ok' => true, 'locale' => 'tj']]);
});

test('admin can access api token management page', function () {
    $admin = createAdminWithTwoFactorForApi();

    $this->actingAs($admin)
        ->get('/adminjon/api-token')
        ->assertSuccessful();
});

test('editor cannot access api token management page', function () {
    $editor = User::factory()->create();
    $editor->assignRole('editor');

    $this->actingAs($editor)
        ->get('/adminjon/api-token')
        ->assertForbidden();
});
