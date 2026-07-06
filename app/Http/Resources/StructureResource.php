<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * GET /structure — composed payload (docs/API-CONTRACT.md §GET /structure):
 * { data: { leadership, departments, offices } }. The controller passes a
 * prepared shape ['leadership' => Collection, 'departments' => Collection,
 * 'offices' => Collection].
 */
class StructureResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'leadership' => LeaderResource::collection($this->resource['leadership']),
            'departments' => DepartmentResource::collection($this->resource['departments']),
            'offices' => OfficeResource::collection($this->resource['offices']),
        ];
    }
}
