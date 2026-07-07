<?php

use App\Enums\NewsStatus;
use App\Filament\Resources\News\Pages\CreateNews;
use App\Models\MediaAsset;
use App\Models\News;
use App\Models\NewsCategory;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('the cover image comes from the linked library asset', function () {
    Queue::fake();
    Storage::fake('public');

    $asset = MediaAsset::factory()->withImage()->create();
    $news = News::factory()->create(['cover_media_asset_id' => $asset->id]);

    expect($news->fresh()->imageSet())->toEqual($asset->fresh()->imageSet());
});

test('a single library image can be reused as the cover of several posts', function () {
    Queue::fake();
    Storage::fake('public');

    $asset = MediaAsset::factory()->withImage()->create();
    News::factory()->create(['slug' => 'a', 'cover_media_asset_id' => $asset->id]);
    News::factory()->create(['slug' => 'b', 'cover_media_asset_id' => $asset->id]);

    expect(News::query()->where('cover_media_asset_id', $asset->id)->count())->toBe(2);
});

test('a gallery holds ordered, reusable library images', function () {
    Queue::fake();
    Storage::fake('public');

    $one = MediaAsset::factory()->withImage()->create();
    $two = MediaAsset::factory()->withImage()->create();

    $first = News::factory()->create();
    $second = News::factory()->create();

    $first->galleryAssets()->attach([$one->id, $two->id]);
    $second->galleryAssets()->attach($one->id); // reuse across posts

    expect($first->galleryAssets)->toHaveCount(2)
        ->and($second->galleryAssets)->toHaveCount(1)
        ->and(DB::table('news_gallery')->where('media_asset_id', $one->id)->count())->toBe(2);
});

test('the API exposes the library cover as an ImageSet (contract unchanged)', function () {
    Queue::fake();
    Storage::fake('public');
    Sanctum::actingAs(User::factory()->create());

    $asset = MediaAsset::factory()->withImage()->create();
    News::factory()->create(['slug' => 'lib-cover', 'cover_media_asset_id' => $asset->id]);

    $this->getJson('/api/v1/news/lib-cover')
        ->assertOk()
        ->assertJsonStructure(['data' => ['image' => ['thumb', 'card', 'hero', 'original']]]);
});

test('the cover picker rejects a non-image library asset', function () {
    Queue::fake();
    Storage::fake('public');
    $this->seed(RoleSeeder::class);

    $category = NewsCategory::factory()->create();
    $document = MediaAsset::factory()->withDocument()->create();

    Livewire::actingAs(createEditorUser())
        ->test(CreateNews::class)
        ->fillForm([
            'title.tj' => 'Заголовок',
            'excerpt.tj' => 'Анонс.',
            'body.tj' => '<p>Текст.</p>',
            'category_id' => $category->id,
            'status' => NewsStatus::Draft->value,
            'cover_media_asset_id' => $document->id,
        ])
        ->call('create')
        ->assertHasFormErrors(['cover_media_asset_id']);
});

test('an editor can pick a library cover and gallery in the news form', function () {
    Queue::fake();
    Storage::fake('public');
    $this->seed(RoleSeeder::class);

    $category = NewsCategory::factory()->create();
    $cover = MediaAsset::factory()->withImage()->create();
    $galleryOne = MediaAsset::factory()->withImage()->create();

    Livewire::actingAs(createEditorUser())
        ->test(CreateNews::class)
        ->fillForm([
            'title.tj' => 'Заголовок',
            'excerpt.tj' => 'Краткий анонс новости.',
            'body.tj' => '<p>Текст новости.</p>',
            'category_id' => $category->id,
            'status' => NewsStatus::Draft->value,
            'cover_media_asset_id' => $cover->id,
            'galleryAssets' => [$galleryOne->id],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $news = News::query()->firstOrFail();

    expect($news->cover_media_asset_id)->toBe($cover->id)
        ->and($news->galleryAssets)->toHaveCount(1)
        ->and($news->galleryAssets->first()->id)->toBe($galleryOne->id);
});
