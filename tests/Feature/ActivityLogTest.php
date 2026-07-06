<?php

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

function createAdminWithTwoFactor(): User
{
    $user = User::factory()->create();
    $user->assignRole('admin');
    $user->saveAppAuthenticationSecret('test-secret');

    return $user;
}

test('admin can access activity log page', function () {
    $admin = createAdminWithTwoFactor();

    $this->actingAs($admin)
        ->get('/adminjon/activity-logs')
        ->assertSuccessful();
});

test('editor cannot access activity log page', function () {
    $editor = User::factory()->create();
    $editor->assignRole('editor');

    $this->actingAs($editor)
        ->get('/adminjon/activity-logs')
        ->assertForbidden();
});

test('user updates are written to activity log', function () {
    $admin = createAdminWithTwoFactor();
    $user = User::factory()->create(['name' => 'Before']);
    $user->assignRole('editor');

    $this->actingAs($admin);

    $user->update(['name' => 'After']);

    $activity = Activity::query()->latest('id')->first();

    expect($activity)->not->toBeNull()
        ->and($activity->subject_type)->toBe(User::class)
        ->and($activity->event)->toBe('updated');
});
