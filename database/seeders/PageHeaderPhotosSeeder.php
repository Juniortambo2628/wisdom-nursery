<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\Testimonial;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Every page header now has its own photo slot in Site settings → Photos.
 * This fills each new slot with the photo the page already showed, so the
 * dashboard shows what is on the site; the Services page gets the photo the
 * client supplied (28 Nov 2026). Also adds the client's first testimonial.
 * Never replaces a photo or testimonial staff have set.
 */
class PageHeaderPhotosSeeder extends Seeder
{
    /** new slot => slot the page used before */
    private const FROM = [
        'image_how_banner' => 'image_facility_2',
        'image_contact_banner' => 'image_story_main',
        'image_quote_banner' => 'image_step_4',
    ];

    public function run(): bool
    {
        if (! is_file(database_path('seeders/data/site/nursery-71.webp'))) {
            return false;
        }

        foreach (self::FROM as $new => $old) {
            if (blank(setting($new)) && filled(setting($old))) {
                Setting::put($new, setting($old));
            }
        }

        if (blank(setting('image_services_banner'))) {
            foreach (['', '-sm'] as $suffix) {
                Storage::disk('public')->put("site/nursery-71{$suffix}.webp", file_get_contents(database_path("seeders/data/site/nursery-71{$suffix}.webp")));
            }
            Setting::put('image_services_banner', 'site/nursery-71.webp');
        }

        $testimonial = [
            'quote' => 'For our compound and boundary landscaping project, we needed sturdy ornamentals like cypress and decorative shrubs that could handle transport and establish cleanly. The plants were properly hardened and delivered on time, saving us costly replacements.',
            'author' => 'Faith W.',
            'context' => 'Site & Landscaping Supervisor, Nairobi, Kenya',
        ];
        $placeholder = Testimonial::where('author', '[Customer name]')->first();
        $placeholder
            ? $placeholder->update($testimonial)
            : Testimonial::firstOrCreate(['author' => 'Faith W.'], $testimonial);

        return true;
    }
}
