<?php

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
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
        ->get('/admin/users')
        ->assertSuccessful();
});

test('editor cannot access users resource', function () {
    $editor = createEditorUser();

    $this->actingAs($editor)
        ->get('/admin/users')
        ->assertForbidden();
});

test('inactive users cannot access filament panel', function () {
    $user = User::factory()->inactive()->create();
    $user->assignRole('admin');

    expect($user->canAccessPanel(Filament::getPanel('admin')))->toBeFalse();
});

test('active staff can access filament panel', function () {
    $admin = createAdminUser();
    $editor = createEditorUser();

    expect($admin->canAccessPanel(Filament::getPanel('admin')))->toBeTrue();
    expect($editor->canAccessPanel(Filament::getPanel('admin')))->toBeTrue();
});

test('public registration route is not available', function () {
    $this->get('/register')->assertNotFound();
});

test('admin can create a new user with role', function () {
    $admin = createAdminUser();

    $this->actingAs($admin)
        ->get('/admin/users/create')
        ->assertSuccessful();

    $user = User::factory()->make();

    Livewire::actingAs($admin)
        ->test(CreateUser::class)
        ->fillForm([
            'name' => $user->name,
            'email' => $user->email,
            'role' => 'editor',
            'password' => 'password1',
            'password_confirmation' => 'password1',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $created = User::query()->where('email', $user->email)->first();

    expect($created)->not->toBeNull()
        ->and($created->is_active)->toBeTrue()
        ->and($created->hasRole('editor'))->toBeTrue();
});

test('creating a user rejects a weak password', function () {
    $admin = createAdminUser();

    Livewire::actingAs($admin)
        ->test(CreateUser::class)
        ->fillForm([
            'name' => 'Слаб',
            'email' => 'weak@khf.tj',
            'role' => 'editor',
            'password' => 'short',
            'password_confirmation' => 'short',
        ])
        ->call('create')
        ->assertHasFormErrors(['password']);

    expect(User::query()->where('email', 'weak@khf.tj')->exists())->toBeFalse();
});

test('the last active admin cannot be demoted to editor', function () {
    $admin = createAdminUser();

    Livewire::actingAs($admin)
        ->test(EditUser::class, ['record' => $admin->getRouteKey()])
        ->fillForm(['role' => 'editor'])
        ->call('save');

    expect($admin->fresh()->hasRole('admin'))->toBeTrue()
        ->and($admin->fresh()->hasRole('editor'))->toBeFalse();
});

test('the last active admin can be edited without changing role', function () {
    $admin = createAdminUser();

    Livewire::actingAs($admin)
        ->test(EditUser::class, ['record' => $admin->getRouteKey()])
        ->fillForm(['name' => 'Обновлённое Имя', 'role' => 'admin'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($admin->fresh()->name)->toBe('Обновлённое Имя')
        ->and($admin->fresh()->hasRole('admin'))->toBeTrue();
});

test('an admin can be demoted while another admin remains', function () {
    $actor = createAdminUser();
    $target = createAdminUser();

    Livewire::actingAs($actor)
        ->test(EditUser::class, ['record' => $target->getRouteKey()])
        ->fillForm(['role' => 'editor'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($target->fresh()->hasRole('editor'))->toBeTrue()
        ->and($target->fresh()->hasRole('admin'))->toBeFalse();
});

test('the deactivate action is hidden for the last active admin', function () {
    $admin = createAdminUser();

    Livewire::actingAs($admin)
        ->test(ListUsers::class)
        ->assertTableActionHidden('deactivate', $admin);

    expect($admin->fresh()->is_active)->toBeTrue();
});
