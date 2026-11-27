<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/** Photo beside the customer quotes on the home page (cypress ready for planting). Leaves a staff upload alone. */
class TestimonialPhotoSeeder extends Seeder
{
    public function run(): bool
    {
        if (! is_file(database_path('seeders/data/site/nursery-65.webp'))) {
            return false;
        }
        if (blank(setting('image_testimonials'))) {
            if (! Storage::disk('public')->exists('site/nursery-65.webp')) {
                Storage::disk('public')->put('site/nursery-65.webp', file_get_contents(database_path('seeders/data/site/nursery-65.webp')));
            }
            Setting::put('image_testimonials', 'site/nursery-65.webp');
        }

        return true;
    }
}
