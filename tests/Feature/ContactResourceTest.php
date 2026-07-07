<?php

use App\Filament\Resources\ContactOffices\Pages\CreateContactOffice;
use App\Filament\Resources\ContactOffices\Pages\EditContactOffice;
use App\Filament\Resources\Hotlines\Pages\CreateHotline;
use App\Models\ContactOffice;
use App\Models\Hotline;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

test('staff can access the hotlines and contact offices resources', function () {
    Hotline::factory()->create();
    ContactOffice::factory()->create();

    $this->actingAs(createAdminUser())->get('/admin/hotlines')->assertSuccessful();
    $this->actingAs(createEditorUser())->get('/admin/hotlines')->assertSuccessful();
    $this->actingAs(createAdminUser())->get('/admin/contact-offices')->assertSuccessful();
    $this->actingAs(createEditorUser())->get('/admin/contact-offices')->assertSuccessful();
});

test('editor can create a hotline', function () {
    Livewire::actingAs(createEditorUser())
        ->test(CreateHotline::class)
        ->fillForm([
            'number' => '112',
            'label.tj' => 'Хадамоти ягонаи наҷот',
            'note.tj' => 'Круглосуточно, бесплатно',
            'is_primary' => true,
            'sort' => 1,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $hotline = Hotline::query()->firstOrFail();

    expect($hotline->number)->toBe('112')
        ->and($hotline->is_primary)->toBeTrue()
        ->and($hotline->getTranslation('label', 'tj'))->toBe('Хадамоти ягонаи наҷот');
});

test('editor can create a contact office', function () {
    Livewire::actingAs(createEditorUser())
        ->test(CreateContactOffice::class)
        ->fillForm([
            'region.tj' => 'вилояти Хатлон',
            'address.tj' => 'ш. Бохтар, кӯчаи Истиқлол 14',
            'hours.tj' => 'Душанбе–Ҷумъа, 8:00–17:00',
            'phone' => '(992 3222) 2-22-12',
            'email' => 'khatlon@khf.tj',
            'is_head' => false,
            'sort' => 1,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $office = ContactOffice::query()->firstOrFail();

    expect($office->phone)->toBe('(992 3222) 2-22-12')
        ->and($office->email)->toBe('khatlon@khf.tj')
        ->and($office->getTranslation('region', 'tj'))->toBe('вилояти Хатлон');
});

test('an invalid email is rejected', function () {
    Livewire::actingAs(createEditorUser())
        ->test(CreateContactOffice::class)
        ->fillForm([
            'region.tj' => 'вилояти Хатлон',
            'address.tj' => 'ш. Бохтар',
            'hours.tj' => 'Душанбе–Ҷумъа',
            'phone' => '(992 3222) 2-22-12',
            'email' => 'not-an-email',
            'is_head' => false,
            'sort' => 1,
        ])
        ->call('create')
        ->assertHasFormErrors(['email']);
});

test('creating a head office through the panel demotes the previous head', function () {
    $existing = ContactOffice::factory()->head()->create();

    Livewire::actingAs(createEditorUser())
        ->test(CreateContactOffice::class)
        ->fillForm([
            'region.tj' => 'Дастгоҳи марказӣ',
            'address.tj' => 'ш. Душанбе',
            'hours.tj' => 'Душанбе–Ҷумъа',
            'phone' => '(992 37) 223-13-11',
            'email' => 'info@khf.tj',
            'is_head' => true,
            'sort' => 1,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect($existing->fresh()->is_head)->toBeFalse()
        ->and(ContactOffice::query()->where('is_head', true)->count())->toBe(1);
});

test('editing a contact office preserves the other locale translations', function () {
    $office = ContactOffice::factory()->create([
        'region' => ['tj' => 'ТҶ', 'ru' => 'РУ', 'en' => 'EN'],
    ]);

    Livewire::actingAs(createAdminUser())
        ->test(EditContactOffice::class, ['record' => $office->getRouteKey()])
        ->assertFormSet([
            'region.ru' => 'РУ',
            'region.en' => 'EN',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($office->fresh()->getTranslation('region', 'ru'))->toBe('РУ');
});
