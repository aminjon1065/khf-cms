<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * GET /forum — composed payload (docs/API-CONTRACT.md §GET /forum):
 * { data: { categories, topics, stats } }. The controller passes a prepared
 * shape ['categories' => Collection, 'topics' => Collection, 'stats' => array].
 */
class ForumResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'categories' => ForumCategoryResource::collection($this->resource['categories']),
            'topics' => ForumTopicResource::collection($this->resource['topics']),
            'stats' => $this->resource['stats'],
        ];
    }
}
