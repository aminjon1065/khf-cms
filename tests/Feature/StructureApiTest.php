<?php

use App\Jobs\SendRevalidationRequest;
use App\Models\Department;
use App\Models\Leader;
use App\Models\RegionalOffice;
use App\Models\User;
use Database\Seeders\StructureSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function actingAsStructureApi(): void
{
    Sanctum::actingAs(User::factory()->create());
}

test('structure endpoint requires a bearer token', function () {
    Leader::factory()->create();

    $this->getJson('/api/v1/structure')->assertUnauthorized();
});

test('returns the contract shape with leadership, departments and offices', function () {
    actingAsStructureApi();
    Leader::factory()->create();
    Department::factory()->create();
    RegionalOffice::factory()->create();

    $this->getJson('/api/v1/structure')
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                'leadership' => [['name', 'role', 'rank', 'bio']],
                'departments' => [['title', 'description', 'head']],
                'offices' => [['region', 'head', 'phone', 'address']],
            ],
        ]);
});

test('optional rank and head are null when empty', function () {
    actingAsStructureApi();
    Leader::factory()->create(['rank' => [], 'sort' => 1]);
    Department::factory()->create(['head' => [], 'sort' => 1]);

    $data = $this->getJson('/api/v1/structure')->assertOk()->json('data');

    expect($data['leadership'][0]['rank'])->toBeNull()
        ->and($data['departments'][0]['head'])->toBeNull();
});

test('only active entries are returned, in manual order', function () {
    actingAsStructureApi();
    Leader::factory()->create(['name' => ['tj' => 'B'], 'sort' => 2]);
    Leader::factory()->create(['name' => ['tj' => 'A'], 'sort' => 1]);
    Leader::factory()->inactive()->create(['name' => ['tj' => 'X'], 'sort' => 0]);

    $leadership = $this->getJson('/api/v1/structure')->assertOk()->json('data.leadership');

    expect($leadership)->toHaveCount(2)
        ->and($leadership[0]['name'])->toBe('A')
        ->and($leadership[1]['name'])->toBe('B');
});

test('translatable fields resolve to the request locale', function () {
    actingAsStructureApi();
    Leader::factory()->create(['role' => ['tj' => 'ТҶ', 'ru' => 'РУ'], 'sort' => 1]);

    expect($this->getJson('/api/v1/structure')->json('data.leadership.0.role'))->toBe('ТҶ')
        ->and($this->getJson('/api/v1/ru/structure')->json('data.leadership.0.role'))->toBe('РУ');
});

test('the structure seeder reproduces the frontend mock', function () {
    $this->seed(StructureSeeder::class);
    actingAsStructureApi();

    $data = $this->getJson('/api/v1/structure')->assertOk()->json('data');

    expect($data['leadership'])->toHaveCount(4)
        ->and($data['departments'])->toHaveCount(6)
        ->and($data['offices'])->toHaveCount(5)
        ->and($data['leadership'][0]['name'])->toBe('Рустам Назарзода')
        ->and($data['leadership'][0]['role'])->toBe('Раиси Кумита')
        ->and($data['leadership'][0]['rank'])->toBe('генерал-лейтенант')
        ->and($data['offices'][0]['region'])->toBe('ш. Душанбе')
        ->and($data['offices'][0]['phone'])->toBe('(992 37) 221-12-12');
});

test('office phone is always a string, never null', function () {
    actingAsStructureApi();
    RegionalOffice::factory()->create(['phone' => null, 'sort' => 1]);

    $office = $this->getJson('/api/v1/structure')->assertOk()->json('data.offices.0');

    expect($office['phone'])->toBe('');
});

test('creating a structure record without an explicit active flag still revalidates', function () {
    Queue::fake();

    Leader::create([
        'name' => ['tj' => 'X'],
        'role' => ['tj' => 'роль'],
        'bio' => ['tj' => 'био'],
        'sort' => 1,
    ]);

    Queue::assertPushed(SendRevalidationRequest::class);
});

test('creating an inactive structure record does not revalidate', function () {
    Queue::fake();

    Leader::factory()->inactive()->create();

    Queue::assertNotPushed(SendRevalidationRequest::class);
});

test('the structure seeder is idempotent', function () {
    $this->seed(StructureSeeder::class);
    $this->seed(StructureSeeder::class);

    expect(Leader::query()->count())->toBe(4)
        ->and(Department::query()->count())->toBe(6)
        ->and(RegionalOffice::query()->count())->toBe(5);
});
