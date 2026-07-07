<?php

use App\Filament\Resources\Slides\Pages\CreateSlide;
use App\Filament\Resources\Slides\Pages\ListSlides;
use App\Jobs\SendRevalidationRequest;
use App\Models\MediaAsset;
use App\Models\Slide;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('the slide image comes from the linked library asset', function () {
    Queue::fake();
    Storage::fake('public');

    $asset = MediaAsset::factory()->withImage()->create();
    $slide = Slide::factory()->create(['image_media_asset_id' => $asset->id]);

    expect($slide->fresh()->imageSet())->toEqual($asset->fresh()->imageSet());
});

test('a library image can be reused by several slides', function () {
    Queue::fake();
    Storage::fake('public');

    $asset = MediaAsset::factory()->withImage()->create();
    Slide::factory()->create(['image_media_asset_id' => $asset->id]);
    Slide::factory()->create(['image_media_asset_id' => $asset->id]);

    expect(Slide::query()->where('image_media_asset_id', $asset->id)->count())->toBe(2);
});

test('the slides API exposes the library image as an ImageSet (contract unchanged)', function () {
    Queue::fake();
    Storage::fake('public');
    Sanctum::actingAs(User::factory()->create());

    $asset = MediaAsset::factory()->withImage()->create();
    Slide::factory()->create(['image_media_asset_id' => $asset->id, 'active' => true]);

    $this->getJson('/api/v1/home/slides')
        ->assertOk()
        ->assertJsonStructure(['data' => [['image' => ['thumb', 'card', 'hero', 'original']]]]);
});

test('an editor can pick a library image for a slide', function () {
    Queue::fake();
    Storage::fake('public');
    $this->seed(RoleSeeder::class);

    $image = MediaAsset::factory()->withImage()->create();

    Livewire::actingAs(createEditorUser())
        ->test(CreateSlide::class)
        ->fillForm([
            'title.tj' => 'Заголовок слайда',
            'category.tj' => 'Спасение',
            'image_media_asset_id' => $image->id,
            'active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Slide::query()->firstOrFail()->image_media_asset_id)->toBe($image->id);
});

test('the slides list shows the library image thumbnail', function () {
    Queue::fake();
    Storage::fake('public');
    $this->seed(RoleSeeder::class);

    $asset = MediaAsset::factory()->withImage()->create();
    $slide = Slide::factory()->create(['image_media_asset_id' => $asset->id]);

    Livewire::actingAs(createAdminUser())
        ->test(ListSlides::class)
        ->assertTableColumnStateSet('image', $slide->imageSet()['thumb'], record: $slide);
});

test('an in-use slide asset cannot be deleted and revalidates when its file changes', function () {
    Storage::fake('public');

    $asset = MediaAsset::factory()->withImage()->create();
    Slide::factory()->create(['image_media_asset_id' => $asset->id, 'active' => true]);

    expect($asset->isInUse())->toBeTrue();
    $asset->delete();
    expect(MediaAsset::query()->whereKey($asset->getKey())->exists())->toBeTrue();

    Queue::fake();
    $asset->clearMediaCollection(MediaAsset::COLLECTION);
    $asset->addMedia(UploadedFile::fake()->image('new.png', 80, 60))
        ->toMediaCollection(MediaAsset::COLLECTION);

    Queue::assertPushed(
        SendRevalidationRequest::class,
        fn (SendRevalidationRequest $job): bool => in_array('home', $job->tags, true),
    );
});
