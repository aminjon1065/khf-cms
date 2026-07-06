<?php

namespace App\Http\Resources;

use App\Models\Slide;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Slide — the exact shape the frontend expects (see docs/API-CONTRACT.md).
 * Translatable fields resolve to the current API locale with a Tajik fallback.
 *
 * @mixin Slide
 */
class SlideResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'category' => $this->category,
            'title' => $this->title,
            'date' => $this->date,
            'source' => $this->source,
            'image' => $this->imageSet(),
            'newsSlug' => $this->news?->slug,
        ];
    }
}
