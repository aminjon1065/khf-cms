<?php

use App\Models\News;
use App\Models\Slide;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('slide translations fall back to tajik', function () {
    $slide = Slide::factory()->create([
        'title' => ['tj' => 'ТҶ', 'ru' => 'РУ'],
    ]);

    app()->setLocale('ru');
    expect($slide->title)->toBe('РУ');

    app()->setLocale('en');
    expect($slide->title)->toBe('ТҶ');
});

test('imageSet is null without media and a full set with one', function () {
    Queue::fake();
    Storage::fake('public');
    $slide = Slide::factory()->create();

    expect($slide->imageSet())->toBeNull();

    $slide->addMedia(UploadedFile::fake()->image('s.jpg', 800, 600))->toMediaCollection(Slide::IMAGE_COLLECTION);

    expect($slide->fresh()->imageSet())->toHaveKeys(['thumb', 'card', 'hero', 'original']);
});

test('activeOrdered scope returns active slides sorted by sort', function () {
    Slide::factory()->create(['sort' => 2]);
    Slide::factory()->create(['sort' => 1]);
    Slide::factory()->inactive()->create(['sort' => 0]);

    expect(Slide::query()->activeOrdered()->pluck('sort')->all())->toBe([1, 2]);
});

test('a slide can link to a news item', function () {
    $news = News::factory()->create(['slug' => 'linked']);
    $slide = Slide::factory()->create(['news_id' => $news->id]);

    expect($slide->news->slug)->toBe('linked');
});
