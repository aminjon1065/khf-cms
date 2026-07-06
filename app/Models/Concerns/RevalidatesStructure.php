<?php

namespace App\Models\Concerns;

use App\Services\RevalidationService;
use Illuminate\Database\Eloquent\Model;

/**
 * Flushes the frontend `structure` ISR tag when a structure record (leadership,
 * department or regional office) changes and it affects public output — i.e. it
 * is active now, or was active before the change (docs/API-CONTRACT.md §5).
 */
trait RevalidatesStructure
{
    public static function bootRevalidatesStructure(): void
    {
        static::saved(function (Model $model): void {
            $model->triggerStructureRevalidation();
        });

        static::deleted(function (Model $model): void {
            $model->triggerStructureRevalidation();
        });
    }

    protected function triggerStructureRevalidation(): void
    {
        if ($this->active || (bool) $this->getRawOriginal('active')) {
            app(RevalidationService::class)->forStructure();
        }
    }
}
