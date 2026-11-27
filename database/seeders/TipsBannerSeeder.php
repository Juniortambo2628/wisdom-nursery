<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/** Grower tips page header photo (supplied 27 Nov 2026). Leaves a photo staff uploaded alone. */
class TipsBannerSeeder extends Seeder
{
    public function run(): bool
    {
        if (! is_file(database_path('seeders/data/site/nursery-70.webp'))) {
            return false;
        }

        if (blank(setting('image_tips_banner'))) {
            foreach (['', '-sm'] as $suffix) {
                Storage::disk('public')->put("site/nursery-70{$suffix}.webp", file_get_contents(database_path("seeders/data/site/nursery-70{$suffix}.webp")));
            }
            Setting::put('image_tips_banner', 'site/nursery-70.webp');
        }

        return true;
    }
}
