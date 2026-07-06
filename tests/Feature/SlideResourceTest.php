<?php

use App\Filament\Resources\Slides\Pages\CreateSlide;
use App\Filament\Resources\Slides\Pages\EditSlide;
use App\Models\Slide;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

test('both admin and editor can access the slide resource', function () {
    Slide::factory()->create();

    $this->actingAs(createAdminUser())->get('/admin/slides')->assertSuccessful();
    $this->actingAs(createEditorUser())->get('/admin/slides')->assertSuccessful();
});

test('a slide can be created with translations', function () {
    Livewire::actingAs(createEditorUser())
        ->test(CreateSlide::class)
        ->fillForm([
            'title.tj' => 'Сарлавҳа',
            'title.ru' => 'Заголовок',
            'category.tj' => 'Наҷот',
            'date' => '14.06.2026',
            'active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $slide = Slide::query()->firstOrFail();

    expect($slide->getTranslation('title', 'ru'))->toBe('Заголовок')
        ->and($slide->date)->toBe('14.06.2026')
        ->and($slide->active)->toBeTrue();
});

test('slide date must match the dd.mm.yyyy format', function () {
    Livewire::actingAs(createEditorUser())
        ->test(CreateSlide::class)
        ->fillForm([
            'title.tj' => 'Сарлавҳа',
            'category.tj' => 'Наҷот',
            'date' => '2026-06-14',
        ])
        ->call('create')
        ->assertHasFormErrors(['date']);
});

test('editing a slide preserves the other locale translations', function () {
    $slide = Slide::factory()->create([
        'title' => ['tj' => 'ТҶ', 'ru' => 'РУ', 'en' => 'EN'],
    ]);

    Livewire::actingAs(createEditorUser())
        ->test(EditSlide::class, ['record' => $slide->getRouteKey()])
        ->assertFormSet(['title.ru' => 'РУ', 'title.en' => 'EN'])
        ->call('save')
        ->assertHasNoFormErrors();

    $slide->refresh();

    expect($slide->getTranslation('title', 'en'))->toBe('EN');
});
