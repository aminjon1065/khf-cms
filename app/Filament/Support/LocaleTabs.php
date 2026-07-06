<?php

namespace App\Filament\Support;

use Closure;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;

/**
 * Builds a per-locale tabbed input for a spatie/laravel-translatable attribute.
 *
 * The default locale (tj) is required (unless $requiredDefault is false); ru/en
 * are optional and fall back to tj in the API. Each field binds to
 * `{attribute}.{locale}`, which spatie stores as a single JSON column.
 */
class LocaleTabs
{
    /**
     * @var array<string, string>
     */
    public const LOCALES = [
        'tj' => 'Тоҷикӣ',
        'ru' => 'Русский',
        'en' => 'English',
    ];

    /**
     * @param  Closure(string $statePath, string $locale, bool $required): Component  $componentUsing
     */
    public static function make(string $attribute, string $label, Closure $componentUsing, bool $requiredDefault = true): Tabs
    {
        $tabs = [];

        foreach (self::LOCALES as $locale => $localeLabel) {
            $required = $requiredDefault && $locale === 'tj';

            $tabs[] = Tab::make($localeLabel)->schema([
                $componentUsing("{$attribute}.{$locale}", $locale, $required),
            ]);
        }

        return Tabs::make($label)->tabs($tabs)->columnSpanFull();
    }

    public static function text(string $attribute, string $label, bool $requiredDefault = true, int $maxLength = 255): Tabs
    {
        return self::make(
            $attribute,
            $label,
            fn (string $statePath, string $locale, bool $required): TextInput => TextInput::make($statePath)
                ->label($label)
                ->required($required)
                ->maxLength($maxLength),
            $requiredDefault,
        );
    }

    public static function textarea(string $attribute, string $label, bool $requiredDefault = true, int $rows = 3): Tabs
    {
        return self::make(
            $attribute,
            $label,
            fn (string $statePath, string $locale, bool $required): Textarea => Textarea::make($statePath)
                ->label($label)
                ->required($required)
                ->rows($rows),
            $requiredDefault,
        );
    }
}
