<?php

namespace App\Http\Resources;

use App\Models\ContactOffice;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A contact office for GET /contacts (docs/API-CONTRACT.md), used for both
 * `headOffice` and each entry of `offices`. Translatable fields resolve to the
 * current locale (fallback tj); phone/email are coalesced to non-null strings
 * to satisfy the frontend Office type.
 *
 * @mixin ContactOffice
 */
class ContactOfficeResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'region' => $this->region,
            'address' => $this->address,
            'phone' => (string) $this->phone,
            'email' => (string) $this->email,
            'hours' => $this->hours,
        ];
    }
}
