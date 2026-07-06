<?php

use App\Filament\Resources\Departments\Pages\CreateDepartment;
use App\Filament\Resources\Leaders\Pages\CreateLeader;
use App\Filament\Resources\Leaders\Pages\EditLeader;
use App\Filament\Resources\RegionalOffices\Pages\CreateRegionalOffice;
use App\Models\Department;
use App\Models\Leader;
use App\Models\RegionalOffice;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

test('staff can access the structure resources', function () {
    Leader::factory()->create();
    Department::factory()->create();
    RegionalOffice::factory()->create();

    foreach (['leaders', 'departments', 'regional-offices'] as $slug) {
        $this->actingAs(createAdminUser())->get("/admin/{$slug}")->assertSuccessful();
        $this->actingAs(createEditorUser())->get("/admin/{$slug}")->assertSuccessful();
    }
});

test('editor can create a leader without a rank', function () {
    Livewire::actingAs(createEditorUser())
        ->test(CreateLeader::class)
        ->fillForm([
            'name.tj' => 'Раис',
            'role.tj' => 'Раиси Кумита',
            'bio.tj' => 'Роҳбарии умумӣ',
            'sort' => 1,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $leader = Leader::query()->firstOrFail();

    expect($leader->getTranslation('name', 'tj'))->toBe('Раис')
        ->and($leader->getTranslationWithoutFallback('rank', 'tj'))->toBe('');
});

test('editor can create a department and a regional office', function () {
    Livewire::actingAs(createEditorUser())
        ->test(CreateDepartment::class)
        ->fillForm([
            'title.tj' => 'Раёсати амалиётӣ',
            'description.tj' => 'Идораи қувваҳо',
            'sort' => 1,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    Livewire::actingAs(createEditorUser())
        ->test(CreateRegionalOffice::class)
        ->fillForm([
            'region.tj' => 'ш. Душанбе',
            'head.tj' => 'полковник А. Раҳимов',
            'address.tj' => 'кӯчаи Лоҳутӣ 26',
            'phone' => '(992 37) 221-12-12',
            'sort' => 1,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Department::query()->count())->toBe(1)
        ->and(RegionalOffice::query()->count())->toBe(1);
});

test('editing a leader preserves the other locale translations', function () {
    $leader = Leader::factory()->create([
        'name' => ['tj' => 'ТҶ', 'ru' => 'РУ', 'en' => 'EN'],
    ]);

    Livewire::actingAs(createAdminUser())
        ->test(EditLeader::class, ['record' => $leader->getRouteKey()])
        ->assertFormSet([
            'name.ru' => 'РУ',
            'name.en' => 'EN',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($leader->fresh()->getTranslation('name', 'ru'))->toBe('РУ');
});
