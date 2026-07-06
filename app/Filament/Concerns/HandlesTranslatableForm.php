<?php

namespace App\Filament\Concerns;

/**
 * Shared handling for Filament edit pages of spatie/laravel-translatable models.
 *
 * On fill we expand each translatable column into its full per-locale array so
 * every locale tab is populated; otherwise editing one locale would overwrite
 * the others with blanks. Empty locales are intentionally passed through on save
 * — spatie filters empty/null translations on read (see HasTranslations::
 * getTranslations), so a blanked locale correctly falls back to the default one.
 */
trait HandlesTranslatableForm
{
    /**
     * @return list<string>
     */
    protected function translatableAttributes(): array
    {
        $model = static::getResource()::getModel();

        return (new $model)->getTranslatableAttributes();
    }
}
