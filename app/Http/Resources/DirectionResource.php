<?php

namespace App\Http\Resources;

use App\Models\Direction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A direction entry for GET /activities (docs/API-CONTRACT.md). `id` is the
 * slug; translatable fields resolve to the current locale (fallback tj).
 *
 * @mixin Direction
 */
class DirectionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->slug,
            'icon' => $this->icon,
            'title' => $this->title,
            'description' => $this->description,
            'stat' => [
                'value' => $this->stat_value,
                'label' => $this->stat_label,
            ],
        ];
    }
}
