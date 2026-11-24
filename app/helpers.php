<?php

use App\Models\Setting;

if (! function_exists('setting')) {
    /** Site setting from the `settings` table (placeholders show as [bracketed] text). */
    function setting(string $key, ?string $default = null): ?string
    {
        return Setting::get($key, $default);
    }
}

if (! function_exists('ksh')) {
    /** 1600 -> "KSh 1,600" */
    function ksh(int|float|null $amount): string
    {
        return 'KSh '.number_format((float) $amount);
    }
}
