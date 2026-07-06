<?php

use App\Enums\NewsStatus;
use App\Filament\Resources\News\Pages\CreateNews;
use App\Filament\Resources\News\Pages\EditNews;
use App\Filament\Resources\News\Pages\ListNews;
use App\Models\News;
use App\Models\NewsCategory;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

test('both admin and editor can access the news resource', function () {
    News::factory()->create();

    $this->actingAs(createAdminUser())->get('/admin/news')->assertSuccessful();
    $this->actingAs(createEditorUser())->get('/admin/news')->assertSuccessful();
});

test('a news item can be created with translations and an auto slug', function () {
    $category = NewsCategory::factory()->create();

    Livewire::actingAs(createEditorUser())
        ->test(CreateNews::class)
        ->fillForm([
            'title.tj' => 'Наҷоти шаҳрвандон',
            'title.ru' => 'Спасение граждан',
            'excerpt.tj' => 'Кӯтоҳ',
            'body.tj' => '<p>Матн</p>',
            'category_id' => $category->id,
            'status' => NewsStatus::Draft->value,
            'author' => 'Пресс-центр КҲФ',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $news = News::query()->firstOrFail();

    expect($news->slug)->toBe('najoti-shahrvandon')
        ->and($news->getTranslation('title', 'ru'))->toBe('Спасение граждан')
        ->and($news->status)->toBe(NewsStatus::Draft);
});

test('editing preserves the other locale translations', function () {
    $news = News::factory()->create([
        'title' => ['tj' => 'ТҶ', 'ru' => 'РУ', 'en' => 'EN'],
    ]);

    Livewire::actingAs(createEditorUser())
        ->test(EditNews::class, ['record' => $news->getRouteKey()])
        ->assertFormSet(['title.ru' => 'РУ', 'title.en' => 'EN'])
        ->call('save')
        ->assertHasNoFormErrors();

    $news->refresh();

    expect($news->getTranslation('title', 'ru'))->toBe('РУ')
        ->and($news->getTranslation('title', 'en'))->toBe('EN');
});

test('autosaveDraft silently persists a draft', function () {
    $news = News::factory()->draft()->create(['title' => ['tj' => 'Draft', 'ru' => 'Черновик']]);

    Livewire::actingAs(createEditorUser())
        ->test(EditNews::class, ['record' => $news->getRouteKey()])
        ->fillForm(['title.ru' => 'Обновлённый черновик'])
        ->call('autosaveDraft');

    $news->refresh();

    expect($news->getTranslation('title', 'ru'))->toBe('Обновлённый черновик');
});

test('autosaveDraft does not touch a published news item', function () {
    $news = News::factory()->create(['title' => ['tj' => 'Pub', 'ru' => 'Опубликовано']]);

    Livewire::actingAs(createEditorUser())
        ->test(EditNews::class, ['record' => $news->getRouteKey()])
        ->fillForm(['title.ru' => 'Не должно сохраниться'])
        ->call('autosaveDraft');

    $news->refresh();

    expect($news->getTranslation('title', 'ru'))->toBe('Опубликовано');
});

test('a news item can be replicated as a draft with a unique slug', function () {
    $news = News::factory()->create([
        'title' => ['tj' => 'Асл'],
        'slug' => 'asl',
        'status' => NewsStatus::Published,
    ]);

    Livewire::actingAs(createEditorUser())
        ->test(ListNews::class)
        ->callTableAction('replicate', $news);

    $replica = News::query()->where('id', '!=', $news->id)->firstOrFail();

    expect(News::query()->count())->toBe(2)
        ->and($replica->status)->toBe(NewsStatus::Draft)
        ->and($replica->slug)->toBe('asl-2');
});

test('the publish bulk action publishes selected drafts', function () {
    $drafts = News::factory()->draft()->count(2)->create();

    Livewire::actingAs(createEditorUser())
        ->test(ListNews::class)
        ->callTableBulkAction('publish', $drafts);

    expect(News::query()->where('status', NewsStatus::Published)->count())->toBe(2);
});
