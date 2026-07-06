<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Tailwind text-colour classes the frontend expects for a news category
 * (`categoryColor` in the API contract). The backing value is sent verbatim.
 */
enum CategoryColor: string implements HasLabel
{
    case Alert = 'text-alert';
    case Brand = 'text-brand';
    case Success = 'text-success';
    case Warn = 'text-warn';

    public function getLabel(): string
    {
        return match ($this) {
            self::Alert => 'Тревога (text-alert)',
            self::Brand => 'Бренд (text-brand)',
            self::Success => 'Успех (text-success)',
            self::Warn => 'Предупреждение (text-warn)',
        };
    }
}
