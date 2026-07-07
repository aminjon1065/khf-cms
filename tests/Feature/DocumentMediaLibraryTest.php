<?php

use App\Enums\DocType;
use App\Enums\DocumentCategory;
use App\Filament\Resources\Documents\Pages\CreateDocument;
use App\Models\Document;
use App\Models\MediaAsset;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('a document linked to a library asset derives its type, size and url from it', function () {
    Queue::fake();
    Storage::fake('public');

    $asset = MediaAsset::factory()->withDocument()->create();
    $document = Document::factory()->create(['media_asset_id' => $asset->id]);

    $document->refresh();

    expect($document->type)->toBe(DocType::Pdf)
        ->and($document->size)->toBe($asset->humanSize())
        ->and($document->fileUrl())->toBe($asset->fileUrl());
});

test('one library document can be reused by several document records', function () {
    Queue::fake();
    Storage::fake('public');

    $asset = MediaAsset::factory()->withDocument()->create();
    Document::factory()->create(['media_asset_id' => $asset->id]);
    Document::factory()->create(['media_asset_id' => $asset->id]);

    expect(Document::query()->where('media_asset_id', $asset->id)->count())->toBe(2);
});

test('the documents API serves the linked library file (contract unchanged)', function () {
    Queue::fake();
    Storage::fake('public');
    Sanctum::actingAs(User::factory()->create());

    $asset = MediaAsset::factory()->withDocument()->create();
    Document::factory()->create(['media_asset_id' => $asset->id, 'category' => DocumentCategory::Laws]);

    $item = $this->getJson('/api/v1/documents')->assertOk()->json('data.items.0');

    expect($item['type'])->toBe('PDF')
        ->and($item['url'])->not->toBeNull();
});

test('an editor can attach a reusable library document without re-uploading', function () {
    Queue::fake();
    Storage::fake('public');
    $this->seed(RoleSeeder::class);

    $asset = MediaAsset::factory()->withDocument()->create();

    Livewire::actingAs(createEditorUser())
        ->test(CreateDocument::class)
        ->fillForm([
            'title.tj' => 'Қонуни ҶТ',
            'category' => DocumentCategory::Laws->value,
            'document_date' => '2020-01-01',
            'media_asset_id' => $asset->id,
            'sort' => 1,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $document = Document::query()->firstOrFail();

    expect($document->media_asset_id)->toBe($asset->id)
        ->and($document->type)->toBe(DocType::Pdf);
});
