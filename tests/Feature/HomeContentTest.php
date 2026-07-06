<?php

use App\Filament\Pages\ManageHomeContent;
use App\Models\HomeSetting;
use App\Models\Service;
use Database\Seeders\HomeContentSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

test('only admin can access the home content page', function () {
    $this->actingAs(createAdminUser())->get('/admin/home-content')->assertSuccessful();
    $this->actingAs(createEditorUser())->get('/admin/home-content')->assertForbidden();
});

test('saving the home content page persists president and stats', function () {
    Livewire::actingAs(createAdminUser())
        ->test(ManageHomeContent::class)
        ->fillForm([
            'president_name' => 'Эмомалӣ Раҳмон',
            'president_role.tj' => 'Президенти ҶТ',
            'president_quote.tj' => '«Цитата»',
            'president_href' => 'https://president.tj',
            'stats_today' => '1 240',
        ])
        ->call('save');

    $home = HomeSetting::current();

    expect($home->president_name)->toBe('Эмомалӣ Раҳмон')
        ->and($home->getTranslation('president_role', 'tj'))->toBe('Президенти ҶТ')
        ->and($home->stats_today)->toBe('1 240');
});

test('the home content seeder reproduces the mock services and president', function () {
    $this->seed(HomeContentSeeder::class);

    expect(Service::query()->count())->toBe(4)
        ->and(Service::query()->where('key', '112')->value('primary'))->toBeTrue()
        ->and(Service::query()->where('key', 'report')->value('route'))->toBe('report');

    $home = HomeSetting::current();

    expect($home->president_name)->toBe('Эмомалӣ Раҳмон')
        ->and($home->getTranslation('president_role', 'tj'))->toBe('Президенти Ҷумҳурии Тоҷикистон')
        ->and($home->stats_reaction)->toBe('8 мин');
});
