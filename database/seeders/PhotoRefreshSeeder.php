<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\GalleryImage;
use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Current nursery photos from the client (27 Nov 2026), replacing the older
 * photos taken from the previous website, which the client says are outdated.
 * Product photos are not touched.
 *
 * Only photos this project seeded (site/nursery-N.webp) are replaced, so
 * anything staff uploaded in the dashboard stays.
 */
class PhotoRefreshSeeder extends Seeder
{
    /** setting key => photo number (database/seeders/data/site/nursery-N.webp) */
    private const SLOTS = [
        'image_hero' => 45,
        'image_story_main' => 48,
        'image_story_small' => 42,
        'image_step_1' => 52,
        'image_step_2' => 47,
        'image_step_3' => 46,
        'image_step_4' => 41,
        'image_shop_banner' => 33,
        'image_about_banner' => 40,
        'image_facility_1' => 32,
        'image_facility_2' => 31,
        'image_facility_wide' => 36,
    ];

    /** Category cards: a nursery photo, or (no current photo yet) one of the category's own product photos. */
    private const CATEGORIES = [
        'fruit-trees' => 'plants/lemon-tree.webp',
        'vegetable-seedlings' => 50,
        'ornamentals-palms' => 30,
        'indoor-plants' => 'plants/swiss-cheese-plant.webp',
        'tree-seedlings' => 43,
    ];

    /** [photo, caption, gallery category]; added after the 27 Nov photos already in the gallery. */
    private const GALLERY = [
        [45, 'Seedling beds under the shade net', 'nursery'],
        [48, 'Cypress ready for planting', 'plants'],
        [52, 'Freshly sown seed trays', 'nursery'],
        [41, 'Young trees ready for dispatch', 'plants'],
        [44, 'Seedling beds', 'nursery'],
        [42, 'Potted seedlings', 'plants'],
        [40, 'Seed trays on the benches', 'nursery'],
        [46, 'Seedlings hardening in the open', 'nursery'],
        [43, 'Tree seedlings in pots', 'plants'],
        [49, 'Rows of young seedlings', 'nursery'],
        [53, 'Timber frame at the nursery', 'nursery'],
    ];

    public function run(): bool
    {
        if (! is_file($this->source(45))) {
            return false; // photos not in the release yet: try again next deploy
        }

        foreach (self::SLOTS as $key => $n) {
            if ($this->seeded(setting($key))) {
                Setting::put($key, $this->copy($n));
            }
        }

        foreach (self::CATEGORIES as $slug => $photo) {
            $category = Category::where('slug', $slug)->first();
            if (! $category || ! $this->seeded($category->image_path)) {
                continue;
            }
            if (is_int($photo)) {
                $category->update(['image_path' => $this->copy($photo)]);
            } elseif (Storage::disk('public')->exists($photo)) {
                $category->update(['image_path' => $photo]);
            }
        }

        // Retire the old gallery photos we seeded (nursery-1 … nursery-29); keep staff uploads and the 27 Nov photos.
        GalleryImage::whereNotNull('path')->get()
            ->filter(fn ($g) => preg_match('#^site/nursery-(\d+)\.webp$#', $g->path, $m) && (int) $m[1] < 30)
            ->each->delete();

        $next = (int) GalleryImage::max('sort_order') + 1;
        foreach (self::GALLERY as $i => [$n, $caption, $category]) {
            GalleryImage::firstOrCreate(['path' => $this->copy($n)], [
                'caption' => $caption, 'category' => $category, 'sort_order' => $next + $i,
            ]);
        }

        return true;
    }

    /** Empty, or a photo this project seeded: safe to replace. */
    private function seeded(?string $path): bool
    {
        return blank($path) || (bool) preg_match('#^site/nursery-\d+\.webp$#', $path);
    }

    private function source(int $n, string $suffix = ''): string
    {
        return database_path("seeders/data/site/nursery-{$n}{$suffix}.webp");
    }

    /** Copies the photo (and its small "-sm" copy for phones, if there is one) to the public disk. */
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
