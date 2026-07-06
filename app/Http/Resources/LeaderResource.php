<?php

namespace App\Http\Resources;

use App\Models\Leader;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A leadership entry for GET /structure (docs/API-CONTRACT.md). `rank` is
 * optional. Translatable fields resolve to the current locale (fallback tj).
 *
 * @mixin Leader
 */
class LeaderResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'role' => $this->role,
            'rank' => filled($this->rank) ? $this->rank : null,
            'bio' => $this->bio,
        ];
    }
}
