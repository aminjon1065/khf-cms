<?php

use App\Filament\Resources\Services\Pages\CreateService;
use App\Filament\Resources\Services\Pages\EditService;
use App\Models\Service;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

test('only admin can access the services resource', function () {
    Service::factory()->create();

    $this->actingAs(createAdminUser())->get('/admin/services')->assertSuccessful();
    $this->actingAs(createEditorUser())->get('/admin/services')->assertForbidden();
});

test('a service can be created with translations', function () {
    Livewire::actingAs(createAdminUser())
        ->test(CreateService::class)
        ->fillForm([
            'key' => 'report',
            'title.tj' => 'Сообщить о ЧС',
            'subtitle.tj' => 'Онлайн-заявка',
            'route' => 'report',
            'active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $service = Service::query()->firstOrFail();

    expect($service->key)->toBe('report')
        ->and($service->getTranslation('title', 'tj'))->toBe('Сообщить о ЧС')
        ->and($service->route)->toBe('report');
});

test('editing a service preserves other locale translations', function () {
    $service = Service::factory()->create(['title' => ['tj' => 'ТҶ', 'ru' => 'РУ']]);

    Livewire::actingAs(createAdminUser())
        ->test(EditService::class, ['record' => $service->getRouteKey()])
        ->assertFormSet(['title.ru' => 'РУ'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($service->refresh()->getTranslation('title', 'ru'))->toBe('РУ');
});
