<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Small (900px) copies of the hero and wide banners, so phones download a
 * fraction of the full image. Only for slots still showing the seeded photo.
 */
class ResponsivePhotosSeeder extends Seeder
{
    public function run(): bool
    {
        if (! is_file(database_path('seeders/data/site/nursery-24-sm.webp'))) {
            return false;
        }

        foreach (['image_hero', 'image_shop_banner', 'image_about_banner'] as $key) {
            $path = Setting::get($key);
            if (! $path || ! preg_match('#^site/(nursery-\d+)\.webp$#', $path, $m)) {
                continue; // replaced by staff: leave it alone
            }
            $source = database_path("seeders/data/site/{$m[1]}-sm.webp");
            if (is_file($source)) {
                Storage::disk('public')->put("site/{$m[1]}-sm.webp", file_get_contents($source));
            }
        }

        return true;
    }
}
