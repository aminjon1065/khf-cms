<?php

namespace App\Http\Resources;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * DocItem — the exact shape the frontend expects (docs/API-CONTRACT.md
 * §GET /documents). Translatable `title` resolves to the current API locale
 * with a Tajik fallback.
 *
 * @mixin Document
 */
class DocumentItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'title' => $this->title,
            'category' => $this->category->value,
            'number' => $this->number,
            'date' => $this->document_date?->format('d.m.Y'),
            'type' => $this->type?->apiValue(),
            'size' => $this->size,
            'url' => $this->fileUrl(),
        ];
    }
}
