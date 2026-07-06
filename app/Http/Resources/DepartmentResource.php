<?php

namespace App\Http\Resources;

use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A department entry for GET /structure (docs/API-CONTRACT.md). `head` is
 * optional. Translatable fields resolve to the current locale (fallback tj).
 *
 * @mixin Department
 */
class DepartmentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'head' => filled($this->head) ? $this->head : null,
        ];
    }
}
