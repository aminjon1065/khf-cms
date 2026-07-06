<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Fixed set of document categories (docs/API-CONTRACT.md §GET /documents,
 * ToR §6.5). The backing value is the category `id` sent to the frontend.
 */
enum DocumentCategory: string implements HasLabel
{
    case Laws = 'laws';
    case Decrees = 'decrees';
    case Orders = 'orders';
    case Guides = 'guides';
    case Reports = 'reports';

    /**
     * Admin (Russian) label for Filament selects/badges.
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::Laws => 'Законы',
            self::Decrees => 'Постановления',
            self::Orders => 'Приказы',
            self::Guides => 'Инструкции',
            self::Reports => 'Отчёты',
        };
    }

    /**
     * Localized public labels for the API `categories` list (tj|ru|en).
     *
     * @return array<string, string>
     */
    public function labels(): array
    {
        return match ($this) {
            self::Laws => ['tj' => 'Қонунҳо', 'ru' => 'Законы', 'en' => 'Laws'],
            self::Decrees => ['tj' => 'Қарорҳо', 'ru' => 'Постановления', 'en' => 'Decrees'],
            self::Orders => ['tj' => 'Фармонҳо', 'ru' => 'Приказы', 'en' => 'Orders'],
            self::Guides => ['tj' => 'Дастурамалҳо', 'ru' => 'Инструкции', 'en' => 'Guides'],
            self::Reports => ['tj' => 'Ҳисоботҳо', 'ru' => 'Отчёты', 'en' => 'Reports'],
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
     * Label for the virtual "all" filter option.
     */
    public static function allLabel(string $locale): string
    {
        return match ($locale) {
            'ru' => 'Все',
            'en' => 'All',
            default => 'Ҳама',
        };
    }
}
