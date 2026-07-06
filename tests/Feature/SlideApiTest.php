<?php

use App\Models\News;
use App\Models\Slide;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function actingAsFrontendUser(): void
{
    Sanctum::actingAs(User::factory()->create());
}

test('home slides endpoint requires a bearer token', function () {
    Slide::factory()->create();

    $this->getJson('/api/v1/home/slides')->assertUnauthorized();
});

test('home slides returns the contract Slide shape wrapped in data', function () {
    actingAsFrontendUser();
    $news = News::factory()->create(['slug' => 'linked-news']);
    Slide::factory()->create(['news_id' => $news->id]);

    $this->getJson('/api/v1/home/slides')
        ->assertOk()
        ->assertJsonStructure([
            'data' => [[
                'id', 'category', 'title', 'date', 'source', 'image', 'newsSlug',
            ]],
        ])
        ->assertJsonPath('data.0.newsSlug', 'linked-news');
});

test('home slides returns only active slides in sort order', function () {
    actingAsFrontendUser();
    Slide::factory()->create(['title' => ['tj' => 'B'], 'sort' => 2]);
    Slide::factory()->create(['title' => ['tj' => 'A'], 'sort' => 1]);
    Slide::factory()->inactive()->create(['title' => ['tj' => 'Hidden'], 'sort' => 0]);

    $titles = collect($this->getJson('/api/v1/home/slides')->assertOk()->json('data'))->pluck('title');

    expect($titles->all())->toBe(['A', 'B']);
});

test('newsSlug is null without a linked news', function () {
    actingAsFrontendUser();
    Slide::factory()->create();

    $this->getJson('/api/v1/home/slides')->assertOk()->assertJsonPath('data.0.newsSlug', null);
});

test('the url locale selects the translation and falls back to tajik', function () {
    actingAsFrontendUser();
    Slide::factory()->create([
        'title' => ['tj' => 'Сарлавҳаи тоҷикӣ', 'ru' => 'Русский заголовок'],
    ]);

    $this->getJson('/api/v1/ru/home/slides')->assertOk()->assertJsonPath('data.0.title', 'Русский заголовок');
    $this->getJson('/api/v1/en/home/slides')->assertOk()->assertJsonPath('data.0.title', 'Сарлавҳаи тоҷикӣ');
    $this->getJson('/api/v1/home/slides')->assertOk()->assertJsonPath('data.0.title', 'Сарлавҳаи тоҷикӣ');
});

test('the tg url segment serves tajik slides (the segment the frontend uses)', function () {
    actingAsFrontendUser();
    Slide::factory()->create(['title' => ['tj' => 'Сарлавҳаи тоҷикӣ', 'ru' => 'Русский заголовок']]);

    $this->getJson('/api/v1/tg/home/slides')->assertOk()->assertJsonPath('data.0.title', 'Сарлавҳаи тоҷикӣ');
});

test('image is null without media and an ImageSet with one', function () {
    actingAsFrontendUser();
    Queue::fake();
    Storage::fake('public');

    Slide::factory()->create(['title' => ['tj' => 'No image'], 'sort' => 1]);
    $withImage = Slide::factory()->create(['title' => ['tj' => 'With image'], 'sort' => 2]);
    $withImage->addMedia(UploadedFile::fake()->image('s.jpg', 800, 600))->toMediaCollection(Slide::IMAGE_COLLECTION);

    $data = $this->getJson('/api/v1/home/slides')
        ->assertOk()
        ->assertJsonPath('data.0.image', null)
        ->json('data');

    expect($data[1]['image'])->toHaveKeys(['thumb', 'card', 'hero', 'original']);
});
