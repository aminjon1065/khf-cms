<?php

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    config()->set('khf.admin', [
        'name' => 'КЧС Админ',
        'email' => 'boss@khf.tj',
        'password' => 'Sup3rSecret!',
    ]);
});

test('database seeder provisions the configured administrator', function () {
    $this->seed(DatabaseSeeder::class);

    $admin = User::query()->where('email', 'boss@khf.tj')->first();

    expect($admin)->not->toBeNull()
        ->and($admin->name)->toBe('КЧС Админ')
        ->and($admin->is_active)->toBeTrue()
        ->and($admin->hasRole('admin'))->toBeTrue()
        ->and(Hash::check('Sup3rSecret!', $admin->password))->toBeTrue();
});

test('database seeder does not duplicate the administrator on re-run', function () {
    $this->seed(DatabaseSeeder::class);
    $this->seed(DatabaseSeeder::class);

    expect(User::query()->where('email', 'boss@khf.tj')->count())->toBe(1);
});

test('re-seeding preserves a rotated password and edited name, and keeps the admin role', function () {
    $this->seed(DatabaseSeeder::class);

    $admin = User::query()->where('email', 'boss@khf.tj')->firstOrFail();
    $admin->forceFill([
        'name' => 'Изменённое Имя',
        'password' => Hash::make('Rotated9999'),
    ])->save();
    $admin->syncRoles([]); // simulate a wiped role assignment

    $this->seed(DatabaseSeeder::class);

    $admin->refresh();

    expect($admin->name)->toBe('Изменённое Имя')
        ->and(Hash::check('Rotated9999', $admin->password))->toBeTrue()
        ->and(Hash::check('Sup3rSecret!', $admin->password))->toBeFalse()
        ->and($admin->hasRole('admin'))->toBeTrue();
});

test('seeder generates a random password when none is configured', function () {
    config()->set('khf.admin.password', null);

    $this->seed(DatabaseSeeder::class);

    $admin = User::query()->where('email', 'boss@khf.tj')->first();

    expect($admin)->not->toBeNull()
        ->and($admin->password)->not->toBeEmpty()
        ->and(Hash::check('Sup3rSecret!', $admin->password))->toBeFalse();
});
