<?php

namespace App\Http\Resources;

use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * NewsItem — the exact shape the frontend expects (see docs/API-CONTRACT.md).
 * Translatable fields resolve to the current API locale with a Tajik fallback.
 *
 * @mixin News
 */
class NewsItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'category' => $this->category?->label,
            'categoryColor' => $this->category?->color?->value,
            'title' => $this->title,
            'date' => $this->published_at?->format('d.m.Y'),
            'excerpt' => $this->excerpt,
            'body' => $this->body,
            'author' => $this->author,
            'views' => $this->views,
            'region' => $this->region?->name,
            'image' => $this->imageSet(),
        ];
    }
}
