<?php

use App\Enums\NewsStatus;
use App\Models\News;
use App\Models\NewsCategory;
use App\Models\Region;
use App\Services\Transliterator;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('transliterator maps the tajik-specific letters', function () {
    expect(Transliterator::slug('ғ ӣ қ ӯ ҳ ҷ'))->toBe('gh-i-q-u-h-j');
});

test('slug is generated from the tajik title by transliteration', function () {
    $news = News::factory()->create([
        'title' => ['tj' => 'Наҷоти шаҳрвандон', 'ru' => 'Спасение граждан'],
        'slug' => null,
    ]);

    expect($news->slug)->toBe('najoti-shahrvandon');
});

test('slug is made unique when titles collide', function () {
    $first = News::factory()->create(['title' => ['tj' => 'Хабари муҳим'], 'slug' => null]);
    $second = News::factory()->create(['title' => ['tj' => 'Хабари муҳим'], 'slug' => null]);

    expect($first->slug)->toBe('khabari-muhim')
        ->and($second->slug)->toBe('khabari-muhim-2');
});

test('an explicitly provided slug is not overwritten', function () {
    $news = News::factory()->create([
        'title' => ['tj' => 'Ягон чиз'],
        'slug' => 'custom-slug',
    ]);

    expect($news->slug)->toBe('custom-slug');
});

test('status is cast to the enum', function () {
    expect(News::factory()->draft()->create()->status)->toBe(NewsStatus::Draft);
});

test('translatable fields fall back to tajik', function () {
    $news = News::factory()->create([
        'title' => ['tj' => 'Танҳо тоҷикӣ'],
    ]);

    expect($news->getTranslation('title', 'en'))->toBe('Танҳо тоҷикӣ')
        ->and($news->getTranslation('title', 'ru'))->toBe('Танҳо тоҷикӣ');
});

test('news belongs to a category and a region', function () {
    $category = NewsCategory::factory()->create();
    $region = Region::factory()->create();

    $news = News::factory()->for($category, 'category')->for($region)->create();

    expect($news->category->is($category))->toBeTrue()
        ->and($news->region->is($region))->toBeTrue();
});

test('region is optional', function () {
    $news = News::factory()->create(['region_id' => null]);

    expect($news->region_id)->toBeNull();
});

test('published scope only returns publicly visible news', function () {
    $published = News::factory()->create();          // published, past date
    $draft = News::factory()->draft()->create();
    $scheduled = News::factory()->scheduled()->create(); // future date
    $archived = News::factory()->archived()->create();

    $ids = News::query()->published()->pluck('id');

    expect($ids->all())->toContain($published->id)
        ->and($ids->all())->not->toContain($draft->id)
        ->and($ids->all())->not->toContain($scheduled->id)
        ->and($ids->all())->not->toContain($archived->id);
});

test('deleting a category with news is blocked', function () {
    $category = NewsCategory::factory()->create();
    News::factory()->for($category, 'category')->create();

    expect(fn () => $category->delete())->toThrow(QueryException::class);
});
