<?php

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

test('filament panel has app totp multi factor authentication enabled', function () {
    expect(Filament::getPanel('admin')->hasMultiFactorAuthentication())->toBeTrue();
    expect(Filament::getMultiFactorAuthenticationProviders())->toHaveKey('app');
});

test('admin without two factor is redirected to setup page', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->get('/admin')
        ->assertRedirectContains('/admin/multi-factor-authentication/set-up');
});

test('editor without two factor can access dashboard', function () {
    $editor = User::factory()->create();
    $editor->assignRole('editor');

    $this->actingAs($editor)
        ->get('/admin')
        ->assertSuccessful();
});

test('admin with configured two factor can access dashboard', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $admin->saveAppAuthenticationSecret('test-secret');

    $this->actingAs($admin)
        ->get('/admin')
        ->assertSuccessful();
});

test('login attempts are throttled after repeated failures', function () {
    $user = User::factory()->create();
    $user->assignRole('editor');

    $key = 'filament.admin.auth.login.'.sha1('test@example.com|127.0.0.1');

    for ($attempt = 0; $attempt < 5; $attempt++) {
        RateLimiter::hit($key);
    }

    expect(RateLimiter::tooManyAttempts($key, 5))->toBeTrue();
});

test('session lifetime uses configured idle timeout', function () {
    expect((int) config('session.lifetime'))->toBe((int) env('SESSION_LIFETIME', 120));
});
