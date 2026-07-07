<?php

namespace App\Models\Concerns;

use App\Services\RevalidationService;
use Illuminate\Database\Eloquent\Model;

/**
 * Flushes the frontend ISR tags returned by revalidationTags() whenever the
 * model changes and it affects public output — i.e. it is active now, or was
 * active before the change (docs/API-CONTRACT.md §5). Consuming models must
 * expose an `active` boolean and implement revalidationTags().
 */
trait RevalidatesContent
{
    public static function bootRevalidatesContent(): void
    {
        static::saved(function (Model $model): void {
            $model->triggerContentRevalidation();
        });

        static::deleted(function (Model $model): void {
            $model->triggerContentRevalidation();
        });
    }

    protected function triggerContentRevalidation(): void
    {
        // A fresh insert has no prior public state (getOriginal reflects the
        // model's $attributes default there), so only revalidate if it is
        // public now. Updates/deletes also cover a record that WAS public
        // (e.g. it was just hidden), which still changes rendered output.
        $wasPublic = ! $this->wasRecentlyCreated && (bool) $this->getOriginal('active');

        if ($this->active || $wasPublic) {
            app(RevalidationService::class)->revalidate($this->revalidationTags());
        }
    }

    /**
     * ISR cache tags to flush for this model.
     *
     * @return list<string>
     */
    abstract protected function revalidationTags(): array;
}
