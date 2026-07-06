<?php

use App\Enums\NewsStatus;
use App\Filament\Resources\News\Pages\EditNews;
use App\Filament\Resources\News\RelationManagers\RevisionsRelationManager;
use App\Models\News;
use App\Models\NewsRevision;
use App\Services\NewsRevisionService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

test('saving a news item records a revision', function () {
    $news = News::factory()->create();

    expect($news->revisions()->count())->toBe(1);
});

test('an unchanged save does not add another revision', function () {
    $news = News::factory()->create();

    $news->save(); // no dirty attributes

    expect($news->revisions()->count())->toBe(1);
});

test('changing a field adds a revision', function () {
    $news = News::factory()->create(['title' => ['tj' => 'V1']]);

    $news->update(['title' => ['tj' => 'V2']]);

    expect($news->revisions()->count())->toBe(2);
});

test('only the last 15 revisions are kept', function () {
    $news = News::factory()->create(['title' => ['tj' => 'V0']]);

    foreach (range(1, 20) as $i) {
        $news->update(['title' => ['tj' => "V{$i}"]]);
    }

    expect($news->revisions()->count())->toBe(15);
});

test('rolling back restores the stored field values', function () {
    $news = News::factory()->create([
        'title' => ['tj' => 'Версия 1', 'ru' => 'РУ 1'],
        'status' => NewsStatus::Published,
    ]);
    $original = $news->revisions()->firstOrFail();

    $news->update(['title' => ['tj' => 'Версия 2'], 'status' => NewsStatus::Archived]);

    app(NewsRevisionService::class)->rollback($original);
    $news->refresh();

    expect($news->getTranslation('title', 'tj'))->toBe('Версия 1')
        ->and($news->getTranslation('title', 'ru'))->toBe('РУ 1')
        ->and($news->status)->toBe(NewsStatus::Published);
});

test('the rollback relation-manager action restores the news item', function () {
    $news = News::factory()->create(['title' => ['tj' => 'Оригинал']]);
    $original = $news->revisions()->firstOrFail();

    $news->update(['title' => ['tj' => 'Изменено']]);

    Livewire::actingAs(createEditorUser())
        ->test(RevisionsRelationManager::class, [
            'ownerRecord' => $news,
            'pageClass' => EditNews::class,
        ])
        ->callTableAction('rollback', $original);

    expect($news->refresh()->getTranslation('title', 'tj'))->toBe('Оригинал');
});

test('deleting a news item cascades to its revisions', function () {
    $news = News::factory()->create();
    expect(NewsRevision::query()->count())->toBe(1);

    $news->delete();

    expect(NewsRevision::query()->count())->toBe(0);
});
