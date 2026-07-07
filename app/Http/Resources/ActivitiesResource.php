<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * GET /activities — composed payload (docs/API-CONTRACT.md §GET /activities):
 * { data: { directions, programs } }. The controller passes a prepared shape
 * ['directions' => Collection, 'programs' => Collection].
 */
class ActivitiesResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'directions' => DirectionResource::collection($this->resource['directions']),
            'programs' => ProgramResource::collection($this->resource['programs']),
        ];
    }
}
