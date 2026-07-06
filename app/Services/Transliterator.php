<?php

namespace App\Services;

use Illuminate\Support\Str;

/**
 * Transliterates Tajik/Russian Cyrillic to Latin and produces a URL slug.
 *
 * Laravel's Str::ascii does not cover the Tajik-specific letters
 * (ғ ӣ қ ӯ ҳ ҷ), so we map them explicitly before slugging.
 */
class Transliterator
{
    /**
     * @var array<string, string>
     */
    private const MAP = [
        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'ғ' => 'gh',
        'д' => 'd', 'е' => 'e', 'ё' => 'yo', 'ж' => 'zh', 'з' => 'z',
        'и' => 'i', 'ӣ' => 'i', 'й' => 'y', 'к' => 'k', 'қ' => 'q',
        'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p',
        'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ӯ' => 'u',
        'ф' => 'f', 'х' => 'kh', 'ҳ' => 'h', 'ц' => 'ts', 'ч' => 'ch',
        'ҷ' => 'j', 'ш' => 'sh', 'щ' => 'shch', 'ъ' => '', 'ы' => 'y',
        'ь' => '', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
    ];

    public static function toLatin(string $text): string
    {
        return strtr(Str::lower($text), self::MAP);
    }

    public static function slug(string $text): string
    {
        return Str::slug(self::toLatin($text));
    }
}
