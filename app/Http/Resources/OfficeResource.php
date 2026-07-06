<?php

namespace App\Http\Resources;

use App\Models\RegionalOffice;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A regional office entry for GET /structure (docs/API-CONTRACT.md).
 * Translatable fields resolve to the current locale (fallback tj).
 *
 * @mixin RegionalOffice
 */
class OfficeResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'region' => $this->region,
            'head' => $this->head,
            // Contract declares phone as a required string; coalesce a stray
            // null to '' so the payload never violates the frontend type.
            'phone' => (string) $this->phone,
            'address' => $this->address,
        ];
    }
}
