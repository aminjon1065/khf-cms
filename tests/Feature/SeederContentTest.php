<?php

use App\Models\News;
use App\Models\Slide;
use App\Models\User;
use Database\Seeders\NewsCategorySeeder;
use Database\Seeders\NewsSeeder;
use Database\Seeders\RegionSeeder;
use Database\Seeders\SlideSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function seedMockContent(): void
{
    test()->seed([NewsCategorySeeder::class, RegionSeeder::class, NewsSeeder::class, SlideSeeder::class]);
}

test('the news seeder reproduces the ten mock items', function () {
    seedMockContent();

    expect(News::query()->count())->toBe(10)
        ->and(News::query()->published()->count())->toBe(10);

    $rescue = News::query()->where('views', 1842)->firstOrFail();

    expect($rescue->getTranslation('title', 'tj'))->toBe('Спасатели вызволили троих граждан из реки Вахш в Хатлонской области')
        ->and($rescue->category->getTranslation('label', 'tj'))->toBe('СПАСЕНИЕ')
        ->and($rescue->category->color->value)->toBe('text-alert')
        ->and($rescue->region->getTranslation('name', 'tj'))->toBe('Хатлон')
        ->and($rescue->author)->toBe('Пресс-центр КҲФ')
        ->and($rescue->published_at->format('d.m.Y'))->toBe('14.06.2026')
        ->and($rescue->body)->toContain('<p>');
});

test('the news seeder is idempotent', function () {
    seedMockContent();
    test()->seed(NewsSeeder::class);

    expect(News::query()->count())->toBe(10);
});

test('the slide seeder reproduces the three mock slides linked to their news', function () {
    seedMockContent();

    $slides = Slide::query()->activeOrdered()->get();

    expect($slides)->toHaveCount(3)
        ->and($slides->pluck('news_id')->filter())->toHaveCount(3)
        ->and($slides->first()->getTranslation('title', 'tj'))->toBe('Спасатели вызволили троих граждан из реки Вахш в Хатлонской области')
        ->and($slides->first()->getTranslation('category', 'tj'))->toBe('Спасение')
        ->and($slides->first()->news->getTranslation('title', 'tj'))->toBe('Спасатели вызволили троих граждан из реки Вахш в Хатлонской области');
});

test('the seeded news are served by the api in date-desc order', function () {
    $this->travelTo(Carbon::create(2026, 7, 6));
    seedMockContent();
    Sanctum::actingAs(User::factory()->create());

    $this->getJson('/api/v1/news')
        ->assertOk()
        ->assertJsonCount(10, 'data')
        ->assertJsonPath('meta.total', 10)
        ->assertJsonPath('data.0.date', '14.06.2026')
        ->assertJsonPath('data.0.category', 'СПАСЕНИЕ')
        ->assertJsonPath('data.0.categoryColor', 'text-alert')
        ->assertJsonPath('data.0.region', 'Хатлон');
});

test('the seeded slides are served with a resolved news slug', function () {
    $this->travelTo(Carbon::create(2026, 7, 6));
    seedMockContent();
    Sanctum::actingAs(User::factory()->create());

    $expectedSlug = News::query()->where('views', 1842)->value('slug');

    $this->getJson('/api/v1/home/slides')
        ->assertOk()
        ->assertJsonCount(3, 'data')
        ->assertJsonPath('data.0.category', 'Спасение')
        ->assertJsonPath('data.0.newsSlug', $expectedSlug);
});
