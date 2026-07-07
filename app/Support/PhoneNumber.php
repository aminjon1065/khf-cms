<?php

namespace App\Support;

/**
 * Normalizes user-entered phone numbers to a canonical `+<digits>` form so the
 * same number written as "(992 37) 221-12-12", "+992 37 221 12 12" or
 * "992372211212" is stored identically (ToR: нормализация телефона).
 */
class PhoneNumber
{
    public static function normalize(string $value): string
    {
        $trimmed = trim($value);
        $digits = preg_replace('/\D+/', '', $trimmed) ?? '';

        // No digits at all — leave as typed; validation handles emptiness.
        if ($digits === '') {
            return $trimmed;
        }

        // Drop an international access prefix (00…) before re-adding a leading +.
        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        // Bare 9-digit Tajik subscriber number → prepend the country code.
        if (strlen($digits) === 9) {
            $digits = '992'.$digits;
        }

        return '+'.$digits;
    }
}
