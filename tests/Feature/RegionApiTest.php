<?php

use App\Enums\RiskLevel;
use App\Jobs\SendRevalidationRequest;
use App\Models\MapRegion;
use App\Models\MapSetting;
use App\Models\User;
use Database\Seeders\MapSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function actingAsRegionApi(): void
{
    Sanctum::actingAs(User::factory()->create());
}

test('regions endpoint requires a bearer token', function () {
    MapRegion::factory()->create();

    $this->getJson('/api/v1/regions')->assertUnauthorized();
});

test('returns the contract shape with regions and stats', function () {
    actingAsRegionApi();
    MapRegion::factory()->create();

    $this->getJson('/api/v1/regions')
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                'regions' => [['id', 'name', 'center', 'risk', 'activeIncidents', 'stations', 'note']],
                'stats' => ['regions', 'stations', 'activeIncidents', 'monitoring'],
            ],
        ]);
});

test('risk is the backing value and id is the slug', function () {
    actingAsRegionApi();
    MapRegion::factory()->create(['slug' => 'dushanbe', 'risk' => RiskLevel::High, 'sort' => 1]);

    $region = $this->getJson('/api/v1/regions')->assertOk()->json('data.regions.0');

    expect($region['id'])->toBe('dushanbe')->and($region['risk'])->toBe('high');
});

test('stats are computed from active regions and monitoring from the singleton', function () {
    actingAsRegionApi();
    MapRegion::factory()->create(['stations' => 10, 'active_incidents' => 2, 'sort' => 1]);
    MapRegion::factory()->create(['stations' => 6, 'active_incidents' => 1, 'sort' => 2]);
    MapRegion::factory()->inactive()->create(['stations' => 99, 'active_incidents' => 99]);
    MapSetting::query()->create(['monitoring' => '250+']);

    $data = $this->getJson('/api/v1/regions')->assertOk()->json('data');

    expect($data['regions'])->toHaveCount(2)
        ->and($data['stats']['regions'])->toBe(2)
        ->and($data['stats']['stations'])->toBe(16)
        ->and($data['stats']['activeIncidents'])->toBe(3)
        ->and($data['stats']['monitoring'])->toBe('250+');
});

test('monitoring defaults to an empty string when unset', function () {
    actingAsRegionApi();
    MapRegion::factory()->create();

    expect($this->getJson('/api/v1/regions')->json('data.stats.monitoring'))->toBe('');
});

test('translatable region fields resolve to the request locale', function () {
    actingAsRegionApi();
    MapRegion::factory()->create(['name' => ['tj' => 'ТҶ', 'ru' => 'РУ'], 'sort' => 1]);

    expect($this->getJson('/api/v1/regions')->json('data.regions.0.name'))->toBe('ТҶ')
        ->and($this->getJson('/api/v1/ru/regions')->json('data.regions.0.name'))->toBe('РУ');
});

test('the map seeder reproduces the frontend mock', function () {
    $this->seed(MapSeeder::class);
    actingAsRegionApi();

    $data = $this->getJson('/api/v1/regions')->assertOk()->json('data');

    expect($data['regions'])->toHaveCount(5)
        ->and($data['stats'])->toBe(['regions' => 5, 'stations' => 52, 'activeIncidents' => 12, 'monitoring' => '320+']);

    $dushanbe = collect($data['regions'])->firstWhere('id', 'dushanbe');

    expect($dushanbe['name'])->toBe('ш. Душанбе')
        ->and($dushanbe['center'])->toBe('Душанбе')
        ->and($dushanbe['risk'])->toBe('low')
        ->and($dushanbe['stations'])->toBe(6)
        ->and($dushanbe['activeIncidents'])->toBe(1);
});

test('the map seeder is idempotent', function () {
    $this->seed(MapSeeder::class);
    $this->seed(MapSeeder::class);

    expect(MapRegion::query()->count())->toBe(5)
        ->and(MapSetting::query()->count())->toBe(1);
});

test('creating an active map region revalidates, an inactive one does not', function () {
    Queue::fake();
    MapRegion::factory()->create();
    Queue::assertPushed(
        SendRevalidationRequest::class,
        fn (SendRevalidationRequest $job): bool => in_array('regions', $job->tags, true),
    );

    Queue::fake();
    MapRegion::factory()->inactive()->create();
    Queue::assertNotPushed(SendRevalidationRequest::class);
});

test('saving the map settings singleton revalidates regions', function () {
    Queue::fake();

    MapSetting::query()->create(['monitoring' => '100+']);

    Queue::assertPushed(
        SendRevalidationRequest::class,
        fn (SendRevalidationRequest $job): bool => in_array('regions', $job->tags, true),
    );
});
