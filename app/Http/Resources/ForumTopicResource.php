<?php

namespace App\Http\Resources;

use App\Models\ForumTopic;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A forum topic for GET /forum (docs/API-CONTRACT.md). `id` is the slug;
 * `lastActivity` is a display string; translatable fields resolve to the current
 * locale (fallback tj).
 *
 * @mixin ForumTopic
 */
class ForumTopicResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->slug,
            'title' => $this->title,
            'category' => $this->category,
            'author' => $this->author,
            'replies' => $this->replies,
            'views' => $this->views,
            'lastActivity' => $this->last_activity,
            'pinned' => $this->pinned,
        ];
    }
}
