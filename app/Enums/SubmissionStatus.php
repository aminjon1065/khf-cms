<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * Processing state of a citizen submission (ЧС-заявка, обращение, подписка).
 * Internal only — never exposed by the public API (ToR §4, «Обращения»).
 */
enum SubmissionStatus: string implements HasColor, HasLabel
{
    case New = 'new';
    case InProgress = 'in_progress';
    case Closed = 'closed';

    /**
     * Admin (Russian) label for Filament.
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::New => 'Новое',
            self::InProgress => 'В работе',
            self::Closed => 'Закрыто',
        };
    }

    /**
     * @return string|array<int, string>|null
     */
    public function getColor(): string|array|null
    {
        return match ($this) {
            self::New => 'warning',
            self::InProgress => 'info',
            self::Closed => 'gray',
        };
    }

    /**
     * value => label map for Filament select columns/filters.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $status): array => [$status->value => $status->getLabel()])
            ->all();
    }
}
