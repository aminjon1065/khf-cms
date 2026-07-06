<?php

namespace App\Http\Resources;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Service — the home quick-button shape (docs/API-CONTRACT.md §3). `id` is the
 * model `key`; translatable fields resolve to the current locale (tj fallback).
 *
 * @mixin Service
 */
class ServiceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->key,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'primary' => $this->primary,
            'tel' => $this->tel,
            'route' => $this->route,
        ];
    }
}
