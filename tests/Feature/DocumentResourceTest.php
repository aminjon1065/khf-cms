<?php

use App\Enums\DocType;
use App\Enums\DocumentCategory;
use App\Filament\Resources\Documents\Pages\CreateDocument;
use App\Filament\Resources\Documents\Pages\EditDocument;
use App\Models\Document;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

test('staff can access the documents resource', function () {
    Document::factory()->create();

    $this->actingAs(createAdminUser())->get('/admin/documents')->assertSuccessful();
    $this->actingAs(createEditorUser())->get('/admin/documents')->assertSuccessful();
});

test('editor can create a fileless document with a manual type, size and date', function () {
    Livewire::actingAs(createEditorUser())
        ->test(CreateDocument::class)
        ->fillForm([
            'title.tj' => 'Санади санҷишӣ',
            'category' => 'laws',
            'number' => '№ 7',
            'document_date' => '2024-01-15',
            'type' => 'pdf',
            'size' => '120 КБ',
            'sort' => 1,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $document = Document::query()->firstOrFail();

    expect($document->getTranslation('title', 'tj'))->toBe('Санади санҷишӣ')
        ->and($document->category)->toBe(DocumentCategory::Laws)
        ->and($document->type)->toBe(DocType::Pdf)
        ->and($document->size)->toBe('120 КБ')
        ->and($document->is_active)->toBeTrue();
});

test('a fileless document requires type, size and date so the API never serves null', function () {
    Livewire::actingAs(createEditorUser())
        ->test(CreateDocument::class)
        ->fillForm([
            'title.tj' => 'Бе файл',
            'category' => 'laws',
            'number' => '№ 8',
            'sort' => 1,
        ])
        ->call('create')
        ->assertHasFormErrors(['document_date', 'type', 'size']);

    expect(Document::query()->count())->toBe(0);
});

test('editing a document preserves the other locale translations', function () {
    $document = Document::factory()->create([
        'title' => ['tj' => 'ТҶ', 'ru' => 'РУ', 'en' => 'EN'],
    ]);

    Livewire::actingAs(createAdminUser())
        ->test(EditDocument::class, ['record' => $document->getRouteKey()])
        ->assertFormSet([
            'title.ru' => 'РУ',
            'title.en' => 'EN',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $document->refresh();

    expect($document->getTranslation('title', 'ru'))->toBe('РУ')
        ->and($document->getTranslation('title', 'en'))->toBe('EN');
});

test('formatBytes renders human-readable sizes', function () {
    expect(Document::formatBytes(420 * 1024))->toBe('420 КБ')
        ->and(Document::formatBytes((int) round(1.2 * 1024 * 1024)))->toBe('1,2 МБ')
        ->and(Document::formatBytes(500))->toBe('500 Б');
});

test('DocType resolves from content MIME and from extension', function () {
    expect(DocType::fromMime('application/pdf'))->toBe(DocType::Pdf)
        ->and(DocType::fromMime('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'))->toBe(DocType::Xlsx)
        ->and(DocType::fromMime('text/plain'))->toBeNull()
        ->and(DocType::fromExtension('DOCX'))->toBe(DocType::Docx)
        ->and(DocType::fromExtension('zip'))->toBeNull();
});
