<?php

namespace App\Http\Resources;

use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A programme entry for GET /activities (docs/API-CONTRACT.md). `status` is the
 * localized label; translatable fields resolve to the current locale (fallback tj).
 *
 * @mixin Program
 */
class ProgramResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'title' => $this->title,
            'period' => $this->period,
            'status' => $this->status->label(app()->getLocale()),
            'description' => $this->description,
        ];
    }
}
