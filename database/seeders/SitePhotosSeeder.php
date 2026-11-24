<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\GalleryImage;
use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Real nursery photos (from the old Wisdom Capital nursery page) for the site's
 * photo slots, category cards and gallery. Runs once; staff can change any of
 * them later in Settings → Site settings → Photos and in Content → Gallery.
 */
class SitePhotosSeeder extends Seeder
{
    /** setting key => photo number (database/seeders/data/site/nursery-N.webp) */
    private const SLOTS = [
        'image_hero' => 24,
        'image_story_main' => 1,
        'image_story_small' => 8,
        'image_step_1' => 28,
        'image_step_2' => 26,
        'image_step_3' => 19,
        'image_step_4' => 11,
        'image_shop_banner' => 25,
        'image_about_banner' => 23,
        'image_facility_1' => 22,
        'image_facility_2' => 15,
        'image_facility_wide' => 5,
    ];

    private const CATEGORIES = [
        'fruit-trees' => 20,
        'vegetable-seedlings' => 26,
        'ornamentals-palms' => 12,
        'indoor-plants' => 6,
        'tree-seedlings' => 28,
    ];

    /** [photo, caption, gallery category] */
    private const GALLERY = [
        [1, 'Walking the seedling beds', 'team'],
        [24, 'Seedling beds in the open', 'nursery'],
        [12, 'Cypress and conifers', 'plants'],
        [6, 'Indoor plants in pots', 'plants'],
        [27, 'Rows of tree seedlings', 'nursery'],
        [23, 'Checking the seedling beds', 'team'],
        [14, 'Blue conifers', 'plants'],
        [5, 'The nursery dam', 'nursery'],
        [18, 'Ornamental shrubs', 'plants'],
        [29, 'Tree seedlings ready for planting', 'nursery'],
        [17, 'Palms and cycads', 'plants'],
        [8, 'Potted plants', 'plants'],
    ];

    public function run(): bool
    {
        if (! is_file($this->source(24))) {
            return false; // photos not in the release yet: try again next deploy
        }

        foreach (self::SLOTS as $key => $n) {
            if (! setting($key)) {
                Setting::put($key, $this->copy($n));
            }
        }

        foreach (self::CATEGORIES as $slug => $n) {
            Category::where('slug', $slug)->whereNull('image_path')->update(['image_path' => $this->copy($n)]);
        }

        // Replace only the untouched placeholders (no photo); keep anything staff added.
        GalleryImage::whereNull('path')->delete();
        foreach (self::GALLERY as $i => [$n, $caption, $category]) {
            GalleryImage::firstOrCreate(['path' => $this->copy($n)], [
                'caption' => $caption,
                'category' => $category,
                'sort_order' => $i,
            ]);
        }

        return true;
    }

    private function source(int $n): string
    {
        return database_path("seeders/data/site/nursery-{$n}.webp");
    }

    private function copy(int $n): string
    {
        $target = "site/nursery-{$n}.webp";
        if (! Storage::disk('public')->exists($target)) {
            Storage::disk('public')->put($target, file_get_contents($this->source($n)));
        }

        return $target;
    }
}
