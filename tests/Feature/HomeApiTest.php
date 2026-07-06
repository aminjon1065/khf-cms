<?php

use App\Models\HomeSetting;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function actingAsHomeFrontend(): void
{
    Sanctum::actingAs(User::factory()->create());
}

test('home endpoint requires a bearer token', function () {
    $this->getJson('/api/v1/home')->assertUnauthorized();
});

test('home returns the contract shape', function () {
    actingAsHomeFrontend();
    Service::factory()->create([
        'key' => '112',
        'title' => ['tj' => '112'],
        'subtitle' => ['tj' => 'Экстренный вызов'],
        'tel' => '112',
        'primary' => true,
        'sort' => 1,
    ]);
    HomeSetting::create([
        'president_name' => 'Эмомалӣ Раҳмон',
        'president_role' => ['tj' => 'Президенти ҶТ'],
        'president_quote' => ['tj' => '«Цитата»'],
        'president_href' => 'https://president.tj',
        'stats_today' => '1 240',
        'stats_month' => '38 902',
        'stats_rescued' => '12 480',
        'stats_reaction' => '8 мин',
    ]);

    $this->getJson('/api/v1/home')
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                'services' => [['id', 'title', 'subtitle', 'primary', 'tel', 'route']],
                'president' => ['name', 'role', 'quote', 'href'],
                'stats' => ['today', 'month', 'rescued', 'reaction'],
            ],
        ])
        ->assertJsonPath('data.services.0.id', '112')
        ->assertJsonPath('data.services.0.primary', true)
        ->assertJsonPath('data.president.name', 'Эмомалӣ Раҳмон')
        ->assertJsonPath('data.stats.today', '1 240');
});

test('home services are active-only and sorted', function () {
    actingAsHomeFrontend();
    Service::factory()->create(['key' => 'b', 'sort' => 2]);
    Service::factory()->create(['key' => 'a', 'sort' => 1]);
    Service::factory()->inactive()->create(['key' => 'hidden', 'sort' => 0]);

    $ids = collect($this->getJson('/api/v1/home')->assertOk()->json('data.services'))->pluck('id');

    expect($ids->all())->toBe(['a', 'b']);
});

test('president role falls back to tajik across locales', function () {
    actingAsHomeFrontend();
    HomeSetting::create(['president_role' => ['tj' => 'Раиси Кумита', 'ru' => 'Председатель']]);

    $this->getJson('/api/v1/ru/home')->assertOk()->assertJsonPath('data.president.role', 'Председатель');
    $this->getJson('/api/v1/en/home')->assertOk()->assertJsonPath('data.president.role', 'Раиси Кумита');
    $this->getJson('/api/v1/home')->assertOk()->assertJsonPath('data.president.role', 'Раиси Кумита');
});
