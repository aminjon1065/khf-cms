<?php

use App\Enums\DocType;
use App\Filament\Resources\MediaAssets\Pages\ListMediaAssets;
use App\Jobs\SendRevalidationRequest;
use App\Models\Document;
use App\Models\MediaAsset;
use App\Models\News;
use App\Rules\MediaAssetKind;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('an image asset exposes the ImageSet conversions and no doc type', function () {
    Queue::fake();
    Storage::fake('public');

    $asset = MediaAsset::factory()->withImage()->create();

    expect($asset->isImage())->toBeTrue()
        ->and($asset->kind())->toBe('image')
        ->and($asset->imageSet())->toHaveKeys(['thumb', 'card', 'hero', 'original'])
        ->and($asset->previewUrl())->not->toBeNull()
        ->and($asset->docType())->toBeNull();
});

test('a document asset exposes a doc type and no ImageSet', function () {
    Queue::fake();
    Storage::fake('public');

    $asset = MediaAsset::factory()->withDocument()->create();

    expect($asset->isImage())->toBeFalse()
        ->and($asset->kind())->toBe('document')
        ->and($asset->imageSet())->toBeNull()
        ->and($asset->previewUrl())->toBeNull()
        ->and($asset->docType())->toBe(DocType::Pdf)
        ->and($asset->fileUrl())->not->toBeNull();
});

test('an empty asset reports the empty kind', function () {
    expect(MediaAsset::factory()->create()->kind())->toBe('empty');
});

test('media files are stored under a date-based path', function () {
    Queue::fake();
    Storage::fake('public');

    $media = MediaAsset::factory()->withImage()->create()->file();
    $expected = $media->created_at->format('Y/m/d').'/'.$media->getKey().'/';

    expect($media->getUrl())->toContain($expected)
        ->and($media->getUrl('thumb'))->toContain($expected.'conversions/');
});

test('the image-only rule rejects a document and passes an image', function () {
    Queue::fake();
    Storage::fake('public');

    $image = MediaAsset::factory()->withImage()->create();
    $document = MediaAsset::factory()->withDocument()->create();

    $rule = MediaAssetKind::image();

    $failed = false;
    $rule->validate('cover', $document->id, function () use (&$failed): void {
        $failed = true;
    });
    expect($failed)->toBeTrue();

    $failed = false;
    $rule->validate('cover', $image->id, function () use (&$failed): void {
        $failed = true;
    });
    expect($failed)->toBeFalse();
});

test('bulk upload creates one library asset per file', function () {
    Queue::fake();
    Storage::fake('public');

    $assets = MediaAsset::createFromUploads([
        UploadedFile::fake()->image('photo-one.png', 60, 40),
        UploadedFile::fake()->image('photo-two.png', 60, 40),
    ]);

    expect($assets)->toHaveCount(2)
        ->and(MediaAsset::query()->count())->toBe(2)
        ->and($assets->pluck('name')->all())->toBe(['photo-one', 'photo-two'])
        ->and($assets->every(fn (MediaAsset $asset): bool => $asset->isImage()))->toBeTrue();
});

test('an asset in use cannot be deleted, a free one can', function () {
    Queue::fake();
    Storage::fake('public');

    $used = MediaAsset::factory()->withImage()->create();
    News::factory()->create(['cover_media_asset_id' => $used->id]);

    expect($used->isInUse())->toBeTrue();
    $used->delete();
    expect(MediaAsset::query()->whereKey($used->getKey())->exists())->toBeTrue();

    $free = MediaAsset::factory()->withImage()->create();
    expect($free->isInUse())->toBeFalse();
    $free->delete();
    expect(MediaAsset::query()->whereKey($free->getKey())->exists())->toBeFalse();
});

test('staff can access the media library', function () {
    $this->seed(RoleSeeder::class);

    $this->actingAs(createAdminUser())->get('/admin/media-assets')->assertSuccessful();
    $this->actingAs(createEditorUser())->get('/admin/media-assets')->assertSuccessful();
});

test('replacing an in-use asset file revalidates consuming news and documents', function () {
    Storage::fake('public');

    $coverAsset = MediaAsset::factory()->withImage()->create();
    News::factory()->create(['slug' => 'lib-cover', 'cover_media_asset_id' => $coverAsset->id]);

    $docAsset = MediaAsset::factory()->withDocument()->create();
    Document::factory()->create(['media_asset_id' => $docAsset->id]);

    // Replacing the cover image → the news `image` URLs change → revalidate news.
    Queue::fake();
    $coverAsset->clearMediaCollection(MediaAsset::COLLECTION);
    $coverAsset->addMedia(UploadedFile::fake()->image('new.png', 80, 60))
        ->toMediaCollection(MediaAsset::COLLECTION);

    Queue::assertPushed(
        SendRevalidationRequest::class,
        fn (SendRevalidationRequest $job): bool => in_array('news', $job->tags, true),
    );

    // Replacing the document file → the document `url`/`size` change → revalidate documents.
    Queue::fake();
    $docAsset->clearMediaCollection(MediaAsset::COLLECTION);
    $docAsset->addMediaFromString('%PDF-1.4 a replaced and noticeably larger document body')
        ->usingFileName('doc.pdf')
        ->toMediaCollection(MediaAsset::COLLECTION);

    Queue::assertPushed(
        SendRevalidationRequest::class,
        fn (SendRevalidationRequest $job): bool => in_array('documents', $job->tags, true),
    );
});

test('the bulk delete respects the in-use guard', function () {
    Queue::fake();
    Storage::fake('public');
    $this->seed(RoleSeeder::class);

    $used = MediaAsset::factory()->withImage()->create();
    News::factory()->create(['cover_media_asset_id' => $used->id]);
    $free = MediaAsset::factory()->withImage()->create();

    Livewire::actingAs(createAdminUser())
        ->test(ListMediaAssets::class)
        ->callTableBulkAction('delete', [$used, $free]);

    expect(MediaAsset::query()->whereKey($used->getKey())->exists())->toBeTrue() // in use → survived
        ->and(MediaAsset::query()->whereKey($free->getKey())->exists())->toBeFalse(); // free → deleted
});
