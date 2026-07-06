<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\DocumentCategory;
use App\Http\Controllers\Controller;
use App\Http\Resources\DocumentsResource;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DocumentController extends Controller
{
    /**
     * GET /documents — categories (with per-category counts and a virtual "all")
     * plus the document list, optionally filtered by `category`.
     */
    public function index(Request $request): DocumentsResource
    {
        $locale = app()->getLocale();

        $filter = $request->query('category');
        $filter = is_string($filter) && DocumentCategory::tryFrom($filter) !== null ? $filter : null;

        $items = Document::query()
            ->active()
            ->when($filter !== null, fn ($query) => $query->where('category', $filter))
            ->orderByDesc('document_date')
            ->orderByDesc('id')
            ->get();

        return new DocumentsResource([
            'categories' => $this->categories($locale),
            'items' => $items,
        ]);
    }

    /**
     * The category filter list: a virtual "all" (total) followed by every fixed
     * category with its localized label and active-document count.
     *
     * @return list<array{id: string, label: string, count: int}>
     */
    private function categories(string $locale): array
    {
        /** @var Collection<string, int> $counts */
        $counts = DB::table('documents')
            ->where('is_active', true)
            ->groupBy('category')
            ->selectRaw('category, count(*) as aggregate')
            ->pluck('aggregate', 'category');

        $categories = [[
            'id' => 'all',
            'label' => DocumentCategory::allLabel($locale),
            'count' => (int) $counts->sum(),
        ]];

        foreach (DocumentCategory::cases() as $case) {
            $categories[] = [
                'id' => $case->value,
                'label' => $case->label($locale),
                'count' => (int) ($counts[$case->value] ?? 0),
            ];
        }

        return $categories;
    }
}
