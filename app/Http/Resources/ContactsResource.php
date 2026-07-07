<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * GET /contacts — composed payload (docs/API-CONTRACT.md §GET /contacts):
 * { data: { hotlines, headOffice, offices } }. The controller passes a prepared
 * shape ['hotlines' => Collection, 'headOffice' => ?ContactOffice,
 * 'offices' => Collection].
 */
class ContactsResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'hotlines' => HotlineResource::collection($this->resource['hotlines']),
            'headOffice' => $this->resource['headOffice']
                ? new ContactOfficeResource($this->resource['headOffice'])
                : null,
            'offices' => ContactOfficeResource::collection($this->resource['offices']),
        ];
    }
}
