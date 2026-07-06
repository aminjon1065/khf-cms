<?php

use App\Models\News;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\Conversions\ConversionCollection;
use Spatie\MediaLibrary\Conversions\Jobs\PerformConversionsJob;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileUnacceptableForCollection;

uses(RefreshDatabase::class);

test('imageSet is null when there is no cover', function () {
    expect(News::factory()->create()->imageSet())->toBeNull();
});

test('the cover conversions are defined for the three sizes and left queued', function () {
    Storage::fake('public');
    $news = News::factory()->create();

    $news->addMedia(UploadedFile::fake()->image('cover.jpg', 1600, 1200))
        ->toMediaCollection(News::COVER_COLLECTION);

    $conversions = ConversionCollection::createForMedia($news->getFirstMedia(News::COVER_COLLECTION));

    foreach (['thumb', 'card', 'hero'] as $name) {
        expect($conversions->getByName($name)->shouldBeQueued())->toBeTrue();
    }
});

test('cover conversions run on the queue, not inline', function () {
    Queue::fake();
    Storage::fake('public');
    $news = News::factory()->create();

    $news->addMedia(UploadedFile::fake()->image('cover.jpg', 1600, 1200))
        ->toMediaCollection(News::COVER_COLLECTION);

    Queue::assertPushed(PerformConversionsJob::class);
});

test('adding a cover generates the three webp conversions', function () {
    Storage::fake('public');
    $news = News::factory()->create();

    $news->addMedia(UploadedFile::fake()->image('cover.jpg', 1600, 1200))
        ->toMediaCollection(News::COVER_COLLECTION);

    $media = $news->getFirstMedia(News::COVER_COLLECTION)->refresh();

    expect($media->hasGeneratedConversion('thumb'))->toBeTrue()
        ->and($media->hasGeneratedConversion('card'))->toBeTrue()
        ->and($media->hasGeneratedConversion('hero'))->toBeTrue();
});

test('imageSet returns the four conversion urls', function () {
    Storage::fake('public');
    $news = News::factory()->create();

    $news->addMedia(UploadedFile::fake()->image('cover.jpg', 1600, 1200))
        ->toMediaCollection(News::COVER_COLLECTION);

    $set = $news->fresh()->imageSet();

    expect($set)->toHaveKeys(['thumb', 'card', 'hero', 'original'])
        ->and($set['thumb'])->toContain('thumb')
        ->and($set['card'])->toContain('card')
        ->and($set['hero'])->toContain('hero');
});

test('the cover collection holds a single file', function () {
    Storage::fake('public');
    $news = News::factory()->create();

    $news->addMedia(UploadedFile::fake()->image('a.jpg', 800, 600))->toMediaCollection(News::COVER_COLLECTION);
    $news->addMedia(UploadedFile::fake()->image('b.jpg', 800, 600))->toMediaCollection(News::COVER_COLLECTION);

    expect($news->getMedia(News::COVER_COLLECTION))->toHaveCount(1);
});

test('the cover collection rejects non-image files', function () {
    Storage::fake('public');
    $news = News::factory()->create();

    expect(fn () => $news->addMedia(UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf'))
        ->toMediaCollection(News::COVER_COLLECTION))
        ->toThrow(FileUnacceptableForCollection::class);
});
