<?php

namespace App\Support;

use Filament\Forms\Components\BaseFileUpload;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * Makes photos light: on upload (and for photos already on the site, via
 * `php artisan nursery:optimize-images`) each photo becomes
 *
 *   name.webp        at most 1920 px on the long side, WebP quality 80
 *   name-sm.webp     900 px, for phones (site_image_srcset / media_srcset)
 *   name-thumb.webp  400 px, for grids and the dashboard's photo library
 *
 * A typical 4 MB phone photo ends up around 250 KB, its thumbnail around 20 KB.
 * Needs PHP's GD with WebP; without it photos are stored as uploaded.
 */
class ImageOptimizer
{
    public const MAX = 1920;

    public const VARIANTS = ['sm' => 900, 'thumb' => 400];

    private const QUALITY = 80;

    /** Compress uploads made through this box. */
    public static function onUpload(FileUpload $field): FileUpload
    {
        return $field->saveUploadedFileUsing(function (BaseFileUpload $component, TemporaryUploadedFile $file): ?string {
            if (! $file->exists()) {
                return null;
            }
            $method = $component->getVisibility() === 'public' ? 'storePubliclyAs' : 'storeAs';
            $stored = $file->{$method}($component->getDirectory(), $component->getUploadedFileNameForStorage($file), $component->getDiskName());

            return $component->getDiskName() === 'public' ? static::process($stored) : $stored;
        });
    }

    public static function supported(): bool
    {
        return function_exists('imagewebp') && function_exists('imagecreatefromstring');
    }

    public static function variant(string $path, string $name): string
    {
        return preg_replace('/\.\w+$/', '', $path)."-{$name}.webp";
    }

    /**
     * Optimise one photo on the public disk; returns its (possibly new, .webp) path.
     * The original is replaced only when the WebP is smaller or it had to be resized.
     */
    public static function process(string $path): string
    {
        $disk = Storage::disk('public');
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (! static::supported() || ! in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true) || ! $disk->exists($path)) {
            return $path; // GIFs (may be animated), SVGs and the like are left as they are
        }

        @ini_set('memory_limit', '512M'); // a 12-megapixel photo needs ~50 MB decoded
        $bytes = $disk->get($path);
        $image = @imagecreatefromstring($bytes);
        if (! $image) {
            return $path;
        }
        $image = static::orient($image, $bytes, $ext);
        imagepalettetotruecolor($image);
        imagealphablending($image, false);
        imagesavealpha($image, true);

        $width = imagesx($image);
        $height = imagesy($image);
        $resized = max($width, $height) > self::MAX;
        $main = static::encode(static::fit($image, self::MAX));

        $target = preg_replace('/\.\w+$/', '', $path).'.webp';
        if ($ext !== 'webp' || $resized || strlen($main) < strlen($bytes)) {
            $disk->put($target, $main);
            if ($target !== $path) {
                $disk->delete($path);
            }
        } else {
            $target = $path; // already a small WebP: keep it as it is
        }

        foreach (self::VARIANTS as $name => $size) {
            $disk->put(static::variant($target, $name), static::encode(static::fit($image, $size)));
        }
        imagedestroy($image);

        return $target;
    }

    private static function fit(\GdImage $image, int $max): \GdImage
    {
        $w = imagesx($image);
        $h = imagesy($image);
        if (max($w, $h) <= $max) {
            return $image;
        }
        $scale = $max / max($w, $h);
        $out = imagecreatetruecolor(max(1, (int) round($w * $scale)), max(1, (int) round($h * $scale)));
        imagealphablending($out, false);
        imagesavealpha($out, true);
        imagecopyresampled($out, $image, 0, 0, 0, 0, imagesx($out), imagesy($out), $w, $h);

        return $out;
    }

    private static function encode(\GdImage $image): string
    {
        ob_start();
        imagewebp($image, null, self::QUALITY);

        return (string) ob_get_clean();
    }

    /** Phone photos are often stored sideways with a rotation flag: apply it. */
    private static function orient(\GdImage $image, string $bytes, string $ext): \GdImage
    {
        if (! in_array($ext, ['jpg', 'jpeg'], true) || ! function_exists('exif_read_data')) {
            return $image;
        }
        $exif = @exif_read_data('data://image/jpeg;base64,'.base64_encode($bytes));
        $rotated = match ((int) ($exif['Orientation'] ?? 1)) {
            3 => imagerotate($image, 180, 0),
            6 => imagerotate($image, -90, 0),
            8 => imagerotate($image, 90, 0),
            default => null,
        };

        return $rotated ?: $image;
    }
}
