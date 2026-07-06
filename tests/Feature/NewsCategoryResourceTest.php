<?php

use App\Enums\CategoryColor;
use App\Filament\Resources\NewsCategories\Pages\CreateNewsCategory;
use App\Filament\Resources\NewsCategories\Pages\EditNewsCategory;
use App\Models\NewsCategory;
use Database\Seeders\NewsCategorySeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

test('admin can access news categories resource', function () {
    NewsCategory::factory()->create(); // exercise the table render (columns, badges)

    $this->actingAs(createAdminUser())
        ->get('/adminjon/news-categories')
        ->assertSuccessful();
});

test('editor cannot access news categories resource', function () {
    $this->actingAs(createEditorUser())
        ->get('/adminjon/news-categories')
        ->assertForbidden();
});

test('admin can create a news category with translations', function () {
    Livewire::actingAs(createAdminUser())
        ->test(CreateNewsCategory::class)
        ->fillForm([
            'label.tj' => 'Наҷот',
            'label.ru' => 'Спасение',
            'label.en' => 'Rescue',
            'color' => CategoryColor::Alert->value,
            'sort' => 1,
            'active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $category = NewsCategory::query()->firstOrFail();

    expect($category->getTranslation('label', 'tj'))->toBe('Наҷот')
        ->and($category->getTranslation('label', 'ru'))->toBe('Спасение')
        ->and($category->getTranslation('label', 'en'))->toBe('Rescue')
        ->and($category->color)->toBe(CategoryColor::Alert);
});

test('editing a category preserves the other locale translations', function () {
    $category = NewsCategory::factory()->create([
        'label' => ['tj' => 'ТҶ', 'ru' => 'РУ', 'en' => 'EN'],
        'color' => CategoryColor::Brand,
    ]);

    // Saving without touching ru/en must keep them: proves the locale tabs are
    // pre-filled on edit instead of overwriting the JSON column with blanks.
    Livewire::actingAs(createAdminUser())
        ->test(EditNewsCategory::class, ['record' => $category->getRouteKey()])
        ->assertFormSet([
            'label.ru' => 'РУ',
            'label.en' => 'EN',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $category->refresh();

    expect($category->getTranslation('label', 'tj'))->toBe('ТҶ')
        ->and($category->getTranslation('label', 'ru'))->toBe('РУ')
        ->and($category->getTranslation('label', 'en'))->toBe('EN');
});

test('editing a category can clear a locale so it falls back to tajik', function () {
    $category = NewsCategory::factory()->create([
        'label' => ['tj' => 'ТҶ', 'ru' => 'РУ', 'en' => 'EN'],
        'color' => CategoryColor::Brand,
    ]);

    // Blanking the RU field must actually remove the RU translation, not silently
    // keep the stale value — otherwise the admin can never restore the tj fallback.
    Livewire::actingAs(createAdminUser())
        ->test(EditNewsCategory::class, ['record' => $category->getRouteKey()])
        ->fillForm(['label.ru' => ''])
        ->call('save')
        ->assertHasNoFormErrors();

    $category->refresh();

    expect($category->getTranslationWithoutFallback('label', 'ru'))->toBe('')
        ->and($category->getTranslation('label', 'ru'))->toBe('ТҶ')
        ->and($category->getTranslation('label', 'en'))->toBe('EN');
});

test('missing translations fall back to tajik', function () {
    $category = NewsCategory::factory()->create([
        'label' => ['tj' => 'Танҳо тоҷикӣ'],
    ]);

    expect($category->getTranslation('label', 'ru'))->toBe('Танҳо тоҷикӣ')
        ->and($category->getTranslation('label', 'en'))->toBe('Танҳо тоҷикӣ');
});

test('news category seeder loads the frontend categories', function () {
    $this->seed(NewsCategorySeeder::class);

    $rescue = NewsCategory::query()->where('label->tj', 'СПАСЕНИЕ')->firstOrFail();

    expect(NewsCategory::query()->count())->toBe(10)
        ->and($rescue->color)->toBe(CategoryColor::Alert);
});
