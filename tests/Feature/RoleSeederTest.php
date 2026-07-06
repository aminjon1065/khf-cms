<?php

use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

test('role seeder creates admin and editor roles', function () {
    $this->seed(RoleSeeder::class);

    expect(Role::query()->pluck('name')->sort()->values()->all())
        ->toBe(['admin', 'editor']);

    foreach (['admin', 'editor'] as $roleName) {
        expect(Role::findByName($roleName, 'web')->guard_name)->toBe('web');
    }
});

test('role seeder is idempotent', function () {
    $this->seed(RoleSeeder::class);
    $this->seed(RoleSeeder::class);

    expect(Role::query()->count())->toBe(2);
});
