<?php

namespace App\Http\Resources;

use App\Models\ForumCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A forum category for GET /forum (docs/API-CONTRACT.md). `id` is the slug;
 * translatable fields resolve to the current locale (fallback tj).
 *
 * @mixin ForumCategory
 */
class ForumCategoryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->slug,
            'title' => $this->title,
            'description' => $this->description,
            'topics' => $this->topics,
            'posts' => $this->posts,
            'icon' => $this->icon,
        ];
    }
}
