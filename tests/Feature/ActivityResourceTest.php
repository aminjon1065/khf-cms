<?php

use App\Enums\ProgramStatus;
use App\Filament\Resources\Directions\Pages\CreateDirection;
use App\Filament\Resources\Directions\Pages\EditDirection;
use App\Filament\Resources\Programs\Pages\CreateProgram;
use App\Models\Direction;
use App\Models\Program;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

test('staff can access the activity resources', function () {
    Direction::factory()->create();
    Program::factory()->create();

    foreach (['directions', 'programs'] as $slug) {
        $this->actingAs(createAdminUser())->get("/admin/{$slug}")->assertSuccessful();
        $this->actingAs(createEditorUser())->get("/admin/{$slug}")->assertSuccessful();
    }
});

test('editor can create a direction', function () {
    Livewire::actingAs(createEditorUser())
        ->test(CreateDirection::class)
        ->fillForm([
            'slug' => 'rescue',
            'icon' => 'LifeBuoy',
            'title.tj' => 'Наҷотдиҳӣ',
            'description.tj' => 'Ҷустуҷӯ ва наҷот',
            'stat_value' => '12 480',
            'stat_label.tj' => 'наҷотдодашуда',
            'sort' => 1,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $direction = Direction::query()->firstOrFail();

    expect($direction->slug)->toBe('rescue')
        ->and($direction->icon)->toBe('LifeBuoy')
        ->and($direction->stat_value)->toBe('12 480')
        ->and($direction->getTranslation('title', 'tj'))->toBe('Наҷотдиҳӣ');
});

test('a duplicate direction slug is rejected', function () {
    Direction::factory()->create(['slug' => 'rescue']);

    Livewire::actingAs(createEditorUser())
        ->test(CreateDirection::class)
        ->fillForm([
            'slug' => 'rescue',
            'icon' => 'Flame',
            'title.tj' => 'Дигар',
            'description.tj' => 'Тавсиф',
            'stat_value' => '10',
            'stat_label.tj' => 'воҳид',
            'sort' => 2,
        ])
        ->call('create')
        ->assertHasFormErrors(['slug']);
});

test('editor can create a programme with a status', function () {
    Livewire::actingAs(createEditorUser())
        ->test(CreateProgram::class)
        ->fillForm([
            'title.tj' => 'Стратегия',
            'period' => '2016–2030',
            'status' => ProgramStatus::Active->value,
            'description.tj' => 'Тавсиф',
            'sort' => 1,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $program = Program::query()->firstOrFail();

    expect($program->status)->toBe(ProgramStatus::Active)
        ->and($program->period)->toBe('2016–2030');
});

test('editing a direction preserves the other locale translations', function () {
    $direction = Direction::factory()->create([
        'title' => ['tj' => 'ТҶ', 'ru' => 'РУ', 'en' => 'EN'],
    ]);

    Livewire::actingAs(createAdminUser())
        ->test(EditDirection::class, ['record' => $direction->getRouteKey()])
        ->assertFormSet([
            'title.ru' => 'РУ',
            'title.en' => 'EN',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($direction->fresh()->getTranslation('title', 'ru'))->toBe('РУ');
});
