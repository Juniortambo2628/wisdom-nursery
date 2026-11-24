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

if (! function_exists('site_image')) {
    /** URL of a site photo stored in settings (e.g. "image_hero"), or null to show the labelled placeholder. */
    function site_image(string $key): ?string
    {
        $path = setting($key);

        return $path ? \Illuminate\Support\Facades\Storage::disk('public')->url($path) : null;
    }
}
