<?php

namespace App\Enums;

/**
 * ISR revalidation cache tags shared with the frontend (docs/API-CONTRACT.md §5).
 * Dynamic per-item tags (e.g. `news:{slug}`) are built at dispatch time.
 */
enum RevalidationTag: string
{
    case News = 'news';
    case Home = 'home';
    case Documents = 'documents';
    case Structure = 'structure';
    case Activities = 'activities';
    case Regions = 'regions';
    case Contacts = 'contacts';
    case Forum = 'forum';

    /**
     * Every fixed tag — sent by the "Сбросить весь кеш" action.
     *
     * @return list<string>
     */
    public static function all(): array
    {
        return array_map(fn (self $tag): string => $tag->value, self::cases());
    }
}
