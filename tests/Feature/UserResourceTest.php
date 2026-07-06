<?php

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

// createAdminUser() and createEditorUser() are defined globally in tests/Pest.php.

test('admin can access users resource', function () {
    $admin = createAdminUser();

    $this->actingAs($admin)
        ->get('/adminjon/users')
        ->assertSuccessful();
});

test('editor cannot access users resource', function () {
    $editor = createEditorUser();

    $this->actingAs($editor)
        ->get('/adminjon/users')
        ->assertForbidden();
});

test('inactive users cannot access filament panel', function () {
    $user = User::factory()->inactive()->create();
    $user->assignRole('admin');

    expect($user->canAccessPanel(Filament::getPanel('adminjon')))->toBeFalse();
});

test('active staff can access filament panel', function () {
    $admin = createAdminUser();
    $editor = createEditorUser();

    expect($admin->canAccessPanel(Filament::getPanel('adminjon')))->toBeTrue();
    expect($editor->canAccessPanel(Filament::getPanel('adminjon')))->toBeTrue();
});

test('public registration route is not available', function () {
    $this->get('/register')->assertNotFound();
});

test('admin can create a new user with role', function () {
    $admin = createAdminUser();

    $this->actingAs($admin)
        ->get('/adminjon/users/create')
        ->assertSuccessful();

    $user = User::factory()->make();

    Livewire::actingAs($admin)
        ->test(CreateUser::class)
        ->fillForm([
            'name' => $user->name,
            'email' => $user->email,
            'role' => 'editor',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $created = User::query()->where('email', $user->email)->first();

    expect($created)->not->toBeNull()
        ->and($created->is_active)->toBeTrue()
        ->and($created->hasRole('editor'))->toBeTrue();
});
