<?php

use App\Enums\CategoryColor;
use App\Models\News;
use App\Models\NewsCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function actingAsFrontend(): void
{
    Sanctum::actingAs(User::factory()->create());
}

test('news endpoints require a bearer token', function () {
    News::factory()->create(['slug' => 'x']);

    $this->getJson('/api/v1/news')->assertUnauthorized();
    $this->getJson('/api/v1/news/x')->assertUnauthorized();
    $this->getJson('/api/v1/news/x/related')->assertUnauthorized();
});

test('index returns the contract NewsItem shape and pagination wrapper', function () {
    actingAsFrontend();
    $category = NewsCategory::factory()->create(['label' => ['tj' => 'СПАСЕНИЕ'], 'color' => CategoryColor::Alert]);
    News::factory()->for($category, 'category')->create();

    $this->getJson('/api/v1/news')
        ->assertOk()
        ->assertJsonStructure([
            'data' => [[
                'id', 'slug', 'category', 'categoryColor', 'title', 'date',
                'excerpt', 'body', 'author', 'views', 'region', 'image',
            ]],
            'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            'links' => ['first', 'last', 'prev', 'next'],
        ])
        ->assertJsonPath('data.0.category', 'СПАСЕНИЕ')
        ->assertJsonPath('data.0.categoryColor', 'text-alert');
});

test('index returns only published news sorted by published_at desc', function () {
    actingAsFrontend();
    News::factory()->create(['slug' => 'older', 'published_at' => now()->subDays(5)]);
    News::factory()->create(['slug' => 'newer', 'published_at' => now()->subDay()]);
    News::factory()->draft()->create(['slug' => 'draft']);
    News::factory()->scheduled()->create(['slug' => 'future']);

    $slugs = collect($this->getJson('/api/v1/news')->assertOk()->json('data'))->pluck('slug');

    expect($slugs->all())->toBe(['newer', 'older']);
});

test('date is formatted as dd.mm.yyyy', function () {
    actingAsFrontend();
    News::factory()->create(['slug' => 'd', 'published_at' => '2026-06-14 10:00:00']);

    $this->getJson('/api/v1/news/d')->assertOk()->assertJsonPath('data.date', '14.06.2026');
});

test('index filters by category id and by label', function () {
    actingAsFrontend();
    $category = NewsCategory::factory()->create(['label' => ['tj' => 'СПАСЕНИЕ']]);
    News::factory()->for($category, 'category')->create(['slug' => 'a']);
    News::factory()->create(['slug' => 'b']);

    $this->getJson('/api/v1/news?category='.$category->id)
        ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.slug', 'a');

    // Non-ASCII query values must be percent-encoded, exactly as a browser or
    // the Next.js fetch client sends them (raw UTF-8 in a URL is malformed).
    $this->getJson('/api/v1/news?'.http_build_query(['category' => 'СПАСЕНИЕ']))
        ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.slug', 'a');
});

test('index searches by title', function () {
    actingAsFrontend();
    News::factory()->create(['title' => ['tj' => 'Наҷот дар дарё'], 'slug' => 'rescue']);
    News::factory()->create(['title' => ['tj' => 'Сохтмони роҳ'], 'slug' => 'road']);

    $this->getJson('/api/v1/news?'.http_build_query(['search' => 'дарё']))
        ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.slug', 'rescue');
});

test('per_page limits the page size', function () {
    actingAsFrontend();
    News::factory()->count(5)->create();

    $this->getJson('/api/v1/news?per_page=2')
        ->assertOk()->assertJsonCount(2, 'data')->assertJsonPath('meta.per_page', 2);
});

test('show finds a news item by slug and by id, 404 otherwise', function () {
    actingAsFrontend();
    $news = News::factory()->create(['slug' => 'my-slug']);

    $this->getJson('/api/v1/news/my-slug')->assertOk()->assertJsonPath('data.slug', 'my-slug');
    $this->getJson('/api/v1/news/'.$news->id)->assertOk()->assertJsonPath('data.id', $news->id);
    $this->getJson('/api/v1/news/does-not-exist')->assertNotFound();
});

test('unpublished news is not reachable through the api', function () {
    actingAsFrontend();
    News::factory()->draft()->create(['slug' => 'secret-draft']);

    $this->getJson('/api/v1/news/secret-draft')->assertNotFound();
});

test('related returns same-category news excluding the current one', function () {
    actingAsFrontend();
    $category = NewsCategory::factory()->create();
    News::factory()->for($category, 'category')->create(['slug' => 'current']);
    News::factory()->for($category, 'category')->create(['slug' => 'same-cat']);
    News::factory()->create(['slug' => 'other']);

    $slugs = collect($this->getJson('/api/v1/news/current/related')->assertOk()->json('data'))->pluck('slug');

    expect($slugs->all())->toContain('same-cat')
        ->and($slugs->all())->not->toContain('current')
        ->and($slugs->all())->not->toContain('other');
});

test('the url locale selects the translation and falls back to tajik', function () {
    actingAsFrontend();
    News::factory()->create([
        'title' => ['tj' => 'Сарлавҳаи тоҷикӣ', 'ru' => 'Русский заголовок'],
        'slug' => 'loc',
    ]);

    $this->getJson('/api/v1/ru/news/loc')->assertOk()->assertJsonPath('data.title', 'Русский заголовок');
    $this->getJson('/api/v1/en/news/loc')->assertOk()->assertJsonPath('data.title', 'Сарлавҳаи тоҷикӣ');
    $this->getJson('/api/v1/news/loc')->assertOk()->assertJsonPath('data.title', 'Сарлавҳаи тоҷикӣ');
});

test('the tg url segment serves tajik content (the segment the frontend uses)', function () {
    actingAsFrontend();
    News::factory()->create([
        'title' => ['tj' => 'Сарлавҳаи тоҷикӣ', 'ru' => 'Русский заголовок'],
        'slug' => 'tg-seg',
    ]);

    $this->getJson('/api/v1/tg/news/tg-seg')->assertOk()->assertJsonPath('data.title', 'Сарлавҳаи тоҷикӣ');
});

test('related respects the limit parameter', function () {
    actingAsFrontend();
    $category = NewsCategory::factory()->create();
    News::factory()->for($category, 'category')->create(['slug' => 'cur']);
    News::factory()->for($category, 'category')->count(3)->create();

    $this->getJson('/api/v1/news/cur/related?limit=1')
        ->assertOk()->assertJsonCount(1, 'data');
});

test('image is null without a cover and an ImageSet with one', function () {
    actingAsFrontend();
    Storage::fake('public');

    News::factory()->create(['slug' => 'no-cover']);
    $this->getJson('/api/v1/news/no-cover')->assertOk()->assertJsonPath('data.image', null);

    $withCover = News::factory()->create(['slug' => 'with-cover']);
    $withCover->addMedia(UploadedFile::fake()->image('c.jpg', 800, 600))->toMediaCollection(News::COVER_COLLECTION);

    $this->getJson('/api/v1/news/with-cover')
        ->assertOk()
        ->assertJsonStructure(['data' => ['image' => ['thumb', 'card', 'hero', 'original']]]);
});
