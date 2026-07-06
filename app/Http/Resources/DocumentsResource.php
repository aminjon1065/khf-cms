<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * GET /documents — composed payload (docs/API-CONTRACT.md §GET /documents):
 * { data: { categories, items } }. The controller passes a prepared array
 * shape ['categories' => array, 'items' => Collection<Document>].
 */
class DocumentsResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'categories' => $this->resource['categories'],
            'items' => DocumentItemResource::collection($this->resource['items']),
        ];
    }
}
