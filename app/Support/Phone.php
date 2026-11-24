<?php

namespace App\Support;

/** Kenyan mobile numbers, normalised to 2547XXXXXXXX / 2541XXXXXXXX. */
class Phone
{
    public static function normalise(?string $input): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $input);

        $digits = match (true) {
            str_starts_with($digits, '254') => $digits,
            str_starts_with($digits, '0') => '254'.substr($digits, 1),
            strlen($digits) === 9 => '254'.$digits,
            default => $digits,
        };

        return preg_match('/^254[17]\d{8}$/', $digits) ? $digits : null;
    }

    /** 254712345678 -> 0712 345 678 */
    public static function display(?string $normalised): string
    {
        if (! $normalised || ! preg_match('/^254(\d{3})(\d{3})(\d{3})$/', $normalised, $m)) {
            return (string) $normalised;
        }

        return "0{$m[1]} {$m[2]} {$m[3]}";
    }
}
