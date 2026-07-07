<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;

/**
 * Assigns a human-readable reference ("{PREFIX}-NNNNNN", zero-padded to 6) from
 * the row id right after insert. The follow-up write is quiet so it fires no
 * further model events. Consuming models expose a `reference` column and
 * implement referencePrefix().
 */
trait GeneratesReference
{
    public static function bootGeneratesReference(): void
    {
        static::created(function (Model $model): void {
            if (filled($model->reference)) {
                return;
            }

            $model->reference = $model->referencePrefix().'-'.str_pad((string) $model->getKey(), 6, '0', STR_PAD_LEFT);
            $model->saveQuietly();
        });
    }

    abstract protected function referencePrefix(): string;
}
