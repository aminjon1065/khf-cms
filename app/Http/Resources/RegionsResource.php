<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * GET /regions — composed payload (docs/API-CONTRACT.md §GET /regions):
 * { data: { regions, stats } }. The controller passes a prepared shape
 * ['regions' => Collection<MapRegion>, 'stats' => array].
 */
class RegionsResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'regions' => MapRegionResource::collection($this->resource['regions']),
            'stats' => $this->resource['stats'],
        ];
    }
}
