<?php

namespace App\Http\Resources;

use App\Models\Hotline;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A hotline for GET /contacts (docs/API-CONTRACT.md). Translatable fields
 * resolve to the current locale (fallback tj); `primary` is the optional flag.
 *
 * @mixin Hotline
 */
class HotlineResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'number' => $this->number,
            'label' => $this->label,
            'note' => $this->note,
            'primary' => $this->is_primary,
        ];
    }
}
