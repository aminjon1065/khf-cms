<?php

use App\Enums\RiskLevel;
use App\Filament\Resources\MapRegions\Pages\CreateMapRegion;
use App\Filament\Resources\MapRegions\Pages\EditMapRegion;
use App\Models\MapRegion;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

test('staff can access the map regions resource', function () {
    MapRegion::factory()->create();

    $this->actingAs(createAdminUser())->get('/admin/map-regions')->assertSuccessful();
    $this->actingAs(createEditorUser())->get('/admin/map-regions')->assertSuccessful();
});

test('editor can create a map region', function () {
    Livewire::actingAs(createEditorUser())
        ->test(CreateMapRegion::class)
        ->fillForm([
            'slug' => 'dushanbe',
            'name.tj' => 'ш. Душанбе',
            'center.tj' => 'Душанбе',
            'risk' => RiskLevel::Low->value,
            'active_incidents' => 1,
            'stations' => 6,
            'note.tj' => 'Обстановка стабильная',
            'sort' => 1,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $region = MapRegion::query()->firstOrFail();

    expect($region->slug)->toBe('dushanbe')
        ->and($region->risk)->toBe(RiskLevel::Low)
        ->and($region->stations)->toBe(6)
        ->and($region->active_incidents)->toBe(1);
});

test('a duplicate map region slug is rejected', function () {
    MapRegion::factory()->create(['slug' => 'dushanbe']);

    Livewire::actingAs(createEditorUser())
        ->test(CreateMapRegion::class)
        ->fillForm([
            'slug' => 'dushanbe',
            'name.tj' => 'Дигар',
            'center.tj' => 'Марказ',
            'risk' => RiskLevel::High->value,
            'active_incidents' => 0,
            'stations' => 1,
            'note.tj' => 'Тавсиф',
            'sort' => 2,
        ])
        ->call('create')
        ->assertHasFormErrors(['slug']);
});

test('editing a map region preserves the other locale translations', function () {
    $region = MapRegion::factory()->create([
        'name' => ['tj' => 'ТҶ', 'ru' => 'РУ', 'en' => 'EN'],
    ]);

    Livewire::actingAs(createAdminUser())
        ->test(EditMapRegion::class, ['record' => $region->getRouteKey()])
        ->assertFormSet([
            'name.ru' => 'РУ',
            'name.en' => 'EN',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($region->fresh()->getTranslation('name', 'ru'))->toBe('РУ');
});
