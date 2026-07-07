<?php

use App\Enums\DocType;
use App\Enums\DocumentCategory;
use App\Jobs\SendRevalidationRequest;
use App\Models\Document;
use App\Models\User;
use Database\Seeders\DocumentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function actingAsApiClient(): void
{
    Sanctum::actingAs(User::factory()->create());
}

test('documents endpoint requires a bearer token', function () {
    Document::factory()->create();

    $this->getJson('/api/v1/documents')->assertUnauthorized();
});

test('returns the contract shape with categories and items', function () {
    actingAsApiClient();
    Document::factory()->category(DocumentCategory::Laws)->create();

    $this->getJson('/api/v1/documents')
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                'categories' => [['id', 'label', 'count']],
                'items' => [['id', 'title', 'category', 'number', 'date', 'type', 'size', 'url']],
            ],
        ]);
});

test('categories list a virtual all plus every category with active counts', function () {
    actingAsApiClient();
    Document::factory()->category(DocumentCategory::Laws)->count(2)->create();
    Document::factory()->category(DocumentCategory::Decrees)->count(1)->create();
    Document::factory()->inactive()->category(DocumentCategory::Laws)->create();

    $categories = collect($this->getJson('/api/v1/documents')->assertOk()->json('data.categories'))
        ->keyBy('id');

    expect($categories['all']['count'])->toBe(3)
        ->and($categories['laws']['count'])->toBe(2)
        ->and($categories['decrees']['count'])->toBe(1)
        ->and($categories['orders']['count'])->toBe(0)
        ->and($categories['all']['label'])->toBe('Ҳама')
        ->and($categories['laws']['label'])->toBe('Қонунҳо');
});

test('filtering by category narrows items but keeps full category counts', function () {
    actingAsApiClient();
    Document::factory()->category(DocumentCategory::Laws)->create(['number' => 'L1']);
    Document::factory()->category(DocumentCategory::Decrees)->create(['number' => 'D1']);

    $response = $this->getJson('/api/v1/documents?category=laws')->assertOk();

    expect($response->json('data.items'))->toHaveCount(1)
        ->and($response->json('data.items.0.category'))->toBe('laws')
        ->and(collect($response->json('data.categories'))->firstWhere('id', 'all')['count'])->toBe(2);
});

test('the all filter and unknown categories return every document', function () {
    actingAsApiClient();
    Document::factory()->count(3)->create();

    expect($this->getJson('/api/v1/documents?category=all')->json('data.items'))->toHaveCount(3)
        ->and($this->getJson('/api/v1/documents?category=bogus')->json('data.items'))->toHaveCount(3);
});

test('category labels localize to the request locale', function () {
    actingAsApiClient();
    Document::factory()->category(DocumentCategory::Laws)->create();

    $categories = collect($this->getJson('/api/v1/ru/documents')->assertOk()->json('data.categories'))
        ->keyBy('id');

    expect($categories['all']['label'])->toBe('Все')
        ->and($categories['laws']['label'])->toBe('Законы');
});

test('inactive documents are excluded from the items list', function () {
    actingAsApiClient();
    Document::factory()->create(['number' => 'visible']);
    Document::factory()->inactive()->create(['number' => 'hidden']);

    $items = $this->getJson('/api/v1/documents')->assertOk()->json('data.items');

    expect($items)->toHaveCount(1)->and($items[0]['number'])->toBe('visible');
});

test('date is DD.MM.YYYY and type is uppercased', function () {
    actingAsApiClient();
    Document::factory()->create([
        'document_date' => '2004-07-15',
        'type' => DocType::Pdf,
    ]);

    $item = $this->getJson('/api/v1/documents')->assertOk()->json('data.items.0');

    expect($item['date'])->toBe('15.07.2004')->and($item['type'])->toBe('PDF');
});

test('the document seeder reproduces the twelve mock documents', function () {
    $this->seed(DocumentSeeder::class);
    actingAsApiClient();

    $categories = collect($this->getJson('/api/v1/documents')->assertOk()->json('data.categories'))
        ->keyBy('id');

    expect($categories['all']['count'])->toBe(12)
        ->and($categories['laws']['count'])->toBe(3)
        ->and($categories['decrees']['count'])->toBe(3)
        ->and($categories['orders']['count'])->toBe(2)
        ->and($categories['guides']['count'])->toBe(2)
        ->and($categories['reports']['count'])->toBe(2);

    $stat = Document::query()->where('number', 'ОМ-2026/1')->firstOrFail();

    expect($stat->type)->toBe(DocType::Xlsx)
        ->and($stat->size)->toBe('120 КБ')
        ->and($stat->document_date->format('d.m.Y'))->toBe('05.06.2026')
        ->and($stat->getTranslation('title', 'tj'))->toContain('Маълумоти оморӣ');
});

test('the document seeder is idempotent', function () {
    $this->seed(DocumentSeeder::class);
    $this->seed(DocumentSeeder::class);

    expect(Document::query()->count())->toBe(12);
});

test('creating an active document revalidates, an inactive one does not', function () {
    Queue::fake();
    Document::factory()->create();
    Queue::assertPushed(
        SendRevalidationRequest::class,
        fn (SendRevalidationRequest $job): bool => in_array('documents', $job->tags, true),
    );

    Queue::fake();
    Document::factory()->inactive()->create();
    Queue::assertNotPushed(SendRevalidationRequest::class);
});
