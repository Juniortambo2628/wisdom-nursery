<?php

namespace Database\Seeders;

use App\Models\GalleryImage;
use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Ornamentals photos from the client (27 Nov 2026) for the Our plants and
 * Gallery page headers, and the gallery. Only replaces photos this project
 * seeded (site/nursery-N.webp), so dashboard uploads stay.
 */
class HeroPhotoSeeder extends Seeder
{
    /** setting key => photo number (database/seeders/data/site/nursery-N.webp) */
    private const SLOTS = [
        'image_shop_banner' => 67,
        'image_gallery_banner' => 66,
    ];

    /** [photo, caption, gallery category] */
    private const GALLERY = [
        [67, 'Golden cypress in rows', 'plants'],
        [66, 'Flowering ornamentals', 'plants'],
        [69, 'Cypress beds', 'plants'],
        [64, 'Tall cypress in grow bags', 'plants'],
        [60, 'Flowering ornamentals in bloom', 'plants'],
        [68, 'Young golden cypress', 'plants'],
        [61, 'White flowering ornamentals', 'plants'],
        [65, 'Cypress ready for planting', 'plants'],
        [62, 'Beds of flowering plants', 'plants'],
    ];

    public function run(): bool
    {
        if (! is_file($this->source(67))) {
            return false; // photos not in the release yet: try again next deploy
        }

        foreach (self::SLOTS as $key => $n) {
            $current = setting($key);
            if (blank($current) || preg_match('#^site/nursery-\d+\.webp$#', $current)) {
                Setting::put($key, $this->copy($n));
            }
        }

        $next = (int) GalleryImage::max('sort_order') + 1;
        foreach (self::GALLERY as $i => [$n, $caption, $category]) {
            GalleryImage::firstOrCreate(['path' => $this->copy($n)], [
                'caption' => $caption, 'category' => $category, 'sort_order' => $next + $i,
            ]);
        }

        return true;
    }

    private function source(int $n, string $suffix = ''): string
    {
        return database_path("seeders/data/site/nursery-{$n}{$suffix}.webp");
    }

    private function copy(int $n): string
    {
        foreach (['', '-sm'] as $suffix) {
            $target = "site/nursery-{$n}{$suffix}.webp";
            if (is_file($this->source($n, $suffix)) && ! Storage::disk('public')->exists($target)) {
                Storage::disk('public')->put($target, file_get_contents($this->source($n, $suffix)));
            }
        }

        return "site/nursery-{$n}.webp";
    }
}
