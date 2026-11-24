<?php

namespace Database\Seeders;

use App\Models\GalleryImage;
use Illuminate\Database\Seeder;

/** Labelled placeholders (no path) until the real photos arrive; the caption says what photo goes there. */
class GallerySeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['team', 'Team in the new uniforms'],
            ['team', 'Team at work in the beds'],
            ['plants', 'Indoor plants ready for sale'],
            ['plants', 'Palms in pots'],
            ['nursery', 'Nursery beds from above'],
            ['nursery', 'Shade-net house'],
            ['deliveries', 'Loading a delivery'],
            ['deliveries', 'Seedlings delivered to a school'],
        ];

        foreach ($items as $i => [$category, $caption]) {
            GalleryImage::firstOrCreate(['caption' => $caption], [
                'category' => $category,
                'sort_order' => $i,
            ]);
        }
    }
}
