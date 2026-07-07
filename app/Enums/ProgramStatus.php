<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * Lifecycle of a state programme (docs/API-CONTRACT.md §GET /activities). The
 * API `status` field is the localized label (tj: Амалкунанда | Ба нақша | Анҷомёфта).
 */
enum ProgramStatus: string implements HasColor, HasLabel
{
    case Active = 'active';
    case Planned = 'planned';
    case Completed = 'completed';

    /**
     * Admin (Russian) label for Filament.
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::Active => 'Действует',
            self::Planned => 'Запланирована',
            self::Completed => 'Завершена',
        };
    }

    /**
     * Localized public labels (tj|ru|en).
     *
     * @return array<string, string>
     */
    public function labels(): array
    {
        return match ($this) {
            self::Active => ['tj' => 'Амалкунанда', 'ru' => 'Действует', 'en' => 'Active'],
            self::Planned => ['tj' => 'Ба нақша', 'ru' => 'Запланирована', 'en' => 'Planned'],
            self::Completed => ['tj' => 'Анҷомёфта', 'ru' => 'Завершена', 'en' => 'Completed'],
        };
    }

    /**
     * Public label in the given locale, falling back to Tajik.
     */
    public function label(string $locale): string
    {
        $labels = $this->labels();

        return $labels[$locale] ?? $labels['tj'];
    }

    /**
     * @return string|array<int, string>|null
     */
    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Active => 'success',
            self::Planned => 'warning',
            self::Completed => 'gray',
        };
    }

    /**
     * Resolve the enum from its Tajik label (used when seeding the frontend mock).
     */
    public static function fromTjLabel(string $label): ?self
    {
        foreach (self::cases() as $case) {
            if ($case->labels()['tj'] === $label) {
                return $case;
            }
        }

        return null;
    }
}
