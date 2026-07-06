<?php

namespace Database\Seeders;

use App\Enums\DocType;
use App\Enums\DocumentCategory;
use App\Models\Document;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DocumentSeeder extends Seeder
{
    /**
     * The twelve documents backing the frontend mock (khf-front
     * lib/content/documents.ts). Idempotent, keyed by `number`.
     *
     * @var list<array{title: string, category: DocumentCategory, number: string, date: string, type: DocType, size: string}>
     */
    private const DOCUMENTS = [
        ['title' => 'Қонуни ҶТ «Дар бораи ҳифзи аҳолӣ ва ҳудудҳо аз ҳолатҳои фавқулодда»', 'category' => DocumentCategory::Laws, 'number' => '№ 53', 'date' => '15.07.2004', 'type' => DocType::Pdf, 'size' => '420 КБ'],
        ['title' => 'Қонуни ҶТ «Дар бораи мудофиаи гражданӣ»', 'category' => DocumentCategory::Laws, 'number' => '№ 391', 'date' => '29.12.2010', 'type' => DocType::Pdf, 'size' => '356 КБ'],
        ['title' => 'Қонуни ҶТ «Дар бораи бехатарии оташнишонӣ»', 'category' => DocumentCategory::Laws, 'number' => '№ 1018', 'date' => '23.07.2016', 'type' => DocType::Pdf, 'size' => '298 КБ'],
        ['title' => 'Қарори Ҳукумати ҶТ оид ба тадбирҳои пешгирии офатҳои табиӣ', 'category' => DocumentCategory::Decrees, 'number' => '№ 344', 'date' => '01.08.2020', 'type' => DocType::Pdf, 'size' => '512 КБ'],
        ['title' => 'Қарор дар бораи Стратегияи миллии паст кардани хатари офатҳо', 'category' => DocumentCategory::Decrees, 'number' => '№ 164', 'date' => '29.03.2019', 'type' => DocType::Pdf, 'size' => '1,2 МБ'],
        ['title' => 'Қарор оид ба тасдиқи Низомномаи системаи огоҳии барвақт', 'category' => DocumentCategory::Decrees, 'number' => '№ 89', 'date' => '12.02.2023', 'type' => DocType::Pdf, 'size' => '640 КБ'],
        ['title' => 'Фармони раиси Кумита дар бораи омодагии мавсими селоб', 'category' => DocumentCategory::Orders, 'number' => '№ 21-ф', 'date' => '10.04.2026', 'type' => DocType::Docx, 'size' => '88 КБ'],
        ['title' => 'Фармон оид ба гузаронидани машқҳои мудофиаи гражданӣ', 'category' => DocumentCategory::Orders, 'number' => '№ 17-ф', 'date' => '03.03.2026', 'type' => DocType::Docx, 'size' => '76 КБ'],
        ['title' => 'Дастурамал оид ба рафтори аҳолӣ ҳангоми заминҷунбӣ', 'category' => DocumentCategory::Guides, 'number' => 'Д-04', 'date' => '20.01.2026', 'type' => DocType::Pdf, 'size' => '210 КБ'],
        ['title' => 'Дастурамал оид ба амалиёт ҳангоми селу обхезӣ', 'category' => DocumentCategory::Guides, 'number' => 'Д-06', 'date' => '20.01.2026', 'type' => DocType::Pdf, 'size' => '245 КБ'],
        ['title' => 'Ҳисоботи солонаи Кумита барои соли 2025', 'category' => DocumentCategory::Reports, 'number' => 'ҲС-2025', 'date' => '28.02.2026', 'type' => DocType::Pdf, 'size' => '3,4 МБ'],
        ['title' => 'Маълумоти оморӣ оид ба ҳолатҳои фавқулодда (нимсолаи I)', 'category' => DocumentCategory::Reports, 'number' => 'ОМ-2026/1', 'date' => '05.06.2026', 'type' => DocType::Xlsx, 'size' => '120 КБ'],
    ];

    public function run(): void
    {
        $sort = 0;

        foreach (self::DOCUMENTS as $document) {
            $sort++;

            if (Document::query()->where('number', $document['number'])->exists()) {
                continue;
            }

            Document::create([
                'title' => ['tj' => $document['title']],
                'category' => $document['category'],
                'number' => $document['number'],
                'document_date' => Carbon::createFromFormat('d.m.Y', $document['date'])->startOfDay(),
                'type' => $document['type'],
                'size' => $document['size'],
                'sort' => $sort,
                'is_active' => true,
            ]);
        }
    }
}
