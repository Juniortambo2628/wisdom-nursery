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

if (! function_exists('site_image_srcset')) {
    /** srcset for a site photo when a small "-sm" copy exists next to it (phones get the light one). */
    function site_image_srcset(string $key): ?string
    {
        $path = setting($key);
        if (! $path) {
            return null;
        }
        $small = preg_replace('/(\.\w+)$/', '-sm$1', $path);
        $disk = \Illuminate\Support\Facades\Storage::disk('public');

        return $disk->exists($small) ? $disk->url($small).' 900w, '.$disk->url($path).' 1920w' : null;
    }
}
