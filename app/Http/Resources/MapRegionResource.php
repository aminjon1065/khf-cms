<?php

namespace App\Http\Resources;

use App\Models\MapRegion;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A map region for GET /regions (docs/API-CONTRACT.md). `id` is the slug and
 * `risk` its backing value; translatable fields resolve to the current locale.
 *
 * @mixin MapRegion
 */
class MapRegionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->slug,
            'name' => $this->name,
            'center' => $this->center,
            'risk' => $this->risk->value,
            'activeIncidents' => $this->active_incidents,
            'stations' => $this->stations,
            'note' => $this->note,
        ];
    }
}
