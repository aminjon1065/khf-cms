<?php

namespace App\Rules;

use App\Models\MediaAsset;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validates that the selected media asset id(s) are all of the expected kind —
 * images for the news cover/gallery, documents for the Documents section. The
 * pickers only surface the right kind in their modal grid; this backs it up
 * server-side (and is passed as an object so Filament does not evaluate it).
 */
class MediaAssetKind implements ValidationRule
{
    public function __construct(private bool $image) {}

    public static function image(): self
    {
        return new self(true);
    }

    public static function document(): self
    {
        return new self(false);
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (MediaAsset::selectionContainsImage($value, image: ! $this->image)) {
            $fail($this->image
                ? 'Выберите изображение из медиатеки.'
                : 'Выберите документ из медиатеки.');
        }
    }
}
