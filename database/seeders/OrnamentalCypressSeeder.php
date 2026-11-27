<?php

namespace Database\Seeders;

use App\Enums\Availability;
use App\Enums\SoldAs;
use App\Models\Category;
use App\Models\GalleryImage;
use App\Models\Plant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Italian and lemon cypress, from the client's ornamentals list, with photos of the
 * mature plants as sold (28 Nov 2026), plus that batch's flowering ornamentals and
 * cypress beds for the gallery.
 *
 * No prices were given, so both plants are added HIDDEN with price 0 and stock 0:
 * staff set the price and stock in Plants and switch "Visible" on.
 */
class OrnamentalCypressSeeder extends Seeder
{
    private const PLANTS = [
        'italian-cypress' => ['Italian Cypress', 'Cupressus sempervirens', 'Tall, narrow cypress for avenues, boundaries and formal planting. Sold as mature plants in grow bags.'],
        'lemon-cypress' => ['Lemon Cypress', 'Cupressus macrocarpa', 'Compact golden-green cypress for entrances, borders and containers. Sold as mature plants in grow bags.'],
    ];

    /** [photo, caption, gallery category] */
    private const GALLERY = [
        [72, 'Flowering ornamentals in bloom', 'plants'],
        [77, 'Italian and lemon cypress beds', 'plants'],
        [73, 'Flowering ornamental, ready to plant', 'plants'],
        [78, 'Lemon cypress ready for sale', 'plants'],
        [76, 'Flowering ornamentals in grow bags', 'plants'],
        [79, 'Cypress ready for landscaping', 'plants'],
        [74, 'Red flowering ornamental', 'plants'],
        [80, 'Cypress beds', 'nursery'],
        [75, 'White flowering ornamental', 'plants'],
    ];

    public function run(): bool
    {
        $category = Category::where('slug', 'ornamentals-palms')->value('id');
        if (! $category || ! is_file(database_path('seeders/data/images/lemon-cypress-1.webp'))) {
            return false;
        }

        $disk = Storage::disk('public');
        $sort = (int) Plant::max('sort_order');
        foreach (self::PLANTS as $slug => [$name, $botanical, $description]) {
            $plant = Plant::firstOrCreate(['slug' => $slug], [
                'category_id' => $category,
                'name' => $name,
                'botanical_name' => $botanical,
                'sold_as' => SoldAs::Potted,
                'price' => 0,
                'stock_qty' => 0,
                'availability' => Availability::Ready,
                'description' => $description,
                'is_active' => false, // until staff enter the price
                'sort_order' => ++$sort,
                'source_ref' => 'catalogue-2026-11-28:'.$slug,
            ]);

            if ($plant->images()->doesntExist()) {
                foreach ([1, 2, 3] as $n) {
                    $path = "plants/{$slug}-{$n}.webp";
                    $disk->put($path, file_get_contents(database_path("seeders/data/images/{$slug}-{$n}.webp")));
                    $plant->images()->create(['path' => $path, 'alt' => $name, 'sort_order' => $n]);
                }
            }
        }

        $next = (int) GalleryImage::max('sort_order') + 1;
        foreach (self::GALLERY as $i => [$n, $caption, $group]) {
            $path = "site/nursery-{$n}.webp";
            if (! $disk->exists($path)) {
                $disk->put($path, file_get_contents(database_path("seeders/data/site/nursery-{$n}.webp")));
            }
            GalleryImage::firstOrCreate(['path' => $path], ['caption' => $caption, 'category' => $group, 'sort_order' => $next + $i]);
        }

        return true;
    }
}
