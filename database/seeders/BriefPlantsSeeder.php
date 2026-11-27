<?php

namespace Database\Seeders;

use App\Enums\Availability;
use App\Enums\SoldAs;
use App\Models\Category;
use App\Models\Plant;
use Illuminate\Database\Seeder;

/**
 * Plants named in the client's brief (28 Nov 2026) that were not yet in the shop.
 * Added HIDDEN with price 0 and stock 0: staff add a price, stock and photo in
 * Plants, then switch "Visible" on.
 */
class BriefPlantsSeeder extends Seeder
{
    /** slug => [name, botanical name or null, category slug] */
    private const PLANTS = [
        'bougainvillea' => ['Bougainvillea', null, 'ornamentals-palms'],
        'croton' => ['Croton', 'Codiaeum variegatum', 'ornamentals-palms'],
        'eugenia' => ['Eugenia', null, 'ornamentals-palms'],
        'spider-grass' => ['Spider Grass', null, 'ornamentals-palms'],
        'china-grass' => ['China Grass', null, 'ornamentals-palms'],
        'grafted-orange' => ['Grafted Orange', 'Citrus sinensis', 'fruit-trees'],
    ];

    public function run(): void
    {
        $sort = (int) Plant::max('sort_order');
        foreach (self::PLANTS as $slug => [$name, $botanical, $categorySlug]) {
            $category = Category::where('slug', $categorySlug)->value('id');
            if (! $category || Plant::where('slug', $slug)->exists()) {
                continue;
            }
            Plant::create([
                'category_id' => $category,
                'name' => $name,
                'slug' => $slug,
                'botanical_name' => $botanical,
                'sold_as' => $categorySlug === 'fruit-trees' ? SoldAs::Seedling : SoldAs::Potted,
                'price' => 0,
                'stock_qty' => 0,
                'availability' => Availability::Ready,
                'is_active' => false, // until staff add a price and photo
                'sort_order' => ++$sort,
                'source_ref' => 'brief-2026-11-28:'.$slug,
            ]);
        }
    }
}
