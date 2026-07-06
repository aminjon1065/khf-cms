<?php

use App\Filament\Resources\Regions\Pages\CreateRegion;
use App\Filament\Resources\Regions\Pages\EditRegion;
use App\Models\Region;
use Database\Seeders\RegionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

test('admin can access regions resource', function () {
    Region::factory()->create(); // exercise the table render (columns, badges)

    $this->actingAs(createAdminUser())
        ->get('/adminjon/regions')
        ->assertSuccessful();
});

test('editor cannot access regions resource', function () {
    $this->actingAs(createEditorUser())
        ->get('/adminjon/regions')
        ->assertForbidden();
});

test('admin can create a region with translations', function () {
    Livewire::actingAs(createAdminUser())
        ->test(CreateRegion::class)
        ->fillForm([
            'name.tj' => 'Хатлон',
            'name.ru' => 'Хатлон',
            'name.en' => 'Khatlon',
            'sort' => 1,
            'active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $region = Region::query()->firstOrFail();

    expect($region->getTranslation('name', 'tj'))->toBe('Хатлон')
        ->and($region->getTranslation('name', 'en'))->toBe('Khatlon');
});

test('editing a region preserves the other locale translations', function () {
    $region = Region::factory()->create([
        'name' => ['tj' => 'ТҶ', 'ru' => 'РУ', 'en' => 'EN'],
    ]);

    Livewire::actingAs(createAdminUser())
        ->test(EditRegion::class, ['record' => $region->getRouteKey()])
        ->assertFormSet([
            'name.ru' => 'РУ',
            'name.en' => 'EN',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $region->refresh();

    expect($region->getTranslation('name', 'ru'))->toBe('РУ')
        ->and($region->getTranslation('name', 'en'))->toBe('EN');
});

test('editing a region can clear a locale so it falls back to tajik', function () {
    $region = Region::factory()->create([
        'name' => ['tj' => 'ТҶ', 'ru' => 'РУ', 'en' => 'EN'],
    ]);

    Livewire::actingAs(createAdminUser())
        ->test(EditRegion::class, ['record' => $region->getRouteKey()])
        ->fillForm(['name.ru' => ''])
        ->call('save')
        ->assertHasNoFormErrors();

    $region->refresh();

    expect($region->getTranslationWithoutFallback('name', 'ru'))->toBe('')
        ->and($region->getTranslation('name', 'ru'))->toBe('ТҶ')
        ->and($region->getTranslation('name', 'en'))->toBe('EN');
});

test('region seeder loads the frontend regions', function () {
    $this->seed(RegionSeeder::class);

    expect(Region::query()->count())->toBe(4)
        ->and(Region::query()->where('name->tj', 'Душанбе')->exists())->toBeTrue();
});
