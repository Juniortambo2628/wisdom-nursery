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
        return media_srcset($path);
    }
}

if (! function_exists('site_phones')) {
    /**
     * The nursery's phone numbers, in the order staff set them in Site settings → Contact.
     * Each: ['number' => '0712 345 678', 'label' => 'Orders' or null, 'tel' => '0712345678' or null].
     * `tel` is null for a [bracketed] placeholder, so it shows as text, not a call link.
     * Falls back to the single "phone" setting used before more than one number was allowed.
     */
    function site_phones(): array
    {
        $rows = json_decode((string) setting('phones'), true);
        if (! is_array($rows) || $rows === []) {
            $rows = filled($single = setting('phone')) ? [['number' => $single]] : [];
        }

        return collect($rows)
            ->filter(fn ($row) => is_array($row) && filled($row['number'] ?? null))
            ->map(function (array $row) {
                $number = trim($row['number']);
                $placeholder = str_starts_with($number, '[');

                return [
                    'number' => $number,
                    'label' => filled($row['label'] ?? null) ? trim($row['label']) : null,
                    'tel' => $placeholder ? null : preg_replace('/[^\d+]/', '', $number),
                ];
            })
            ->values()
            ->all();
    }
}

if (! function_exists('media_srcset')) {
    /** srcset for any public-disk photo with the optimiser's -thumb / -sm copies, so small tiles load small files. */
    function media_srcset(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }
        $disk = \Illuminate\Support\Facades\Storage::disk('public');
        $sources = [];
        foreach (\App\Support\ImageOptimizer::VARIANTS as $name => $width) {
            $variant = \App\Support\ImageOptimizer::variant($path, $name);
            if ($disk->exists($variant)) {
                $sources[$width] = $disk->url($variant).' '.$width.'w';
            }
        }
        if (! $sources) {
            return null;
        }
        ksort($sources);
        $sources[] = $disk->url($path).' '.\App\Support\ImageOptimizer::MAX.'w';

        return implode(', ', $sources);
    }
}
