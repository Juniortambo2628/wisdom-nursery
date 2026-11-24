<?php

namespace Database\Seeders;

use App\Enums\Availability;
use App\Enums\SoldAs;
use App\Models\Category;
use App\Models\Plant;
use Illuminate\Database\Seeder;

/**
 * Grafted fruit seedlings from the latest catalogue and prices the client sent
 * on 24 Nov 2026 (WhatsApp). Stock is 0 until the team enters real counts in
 * the dashboard (Plants → "In stock"), so nothing is promised that is not ready.
 */
class FruitTreeSeeder extends Seeder
{
    public function run(): void
    {
        $category = Category::where('slug', 'fruit-trees')->value('id');
        if (! $category) {
            return;
        }

        $plants = [
            ['Grafted Hass Avocado', 'Persea americana', 150],
            ['Grafted Fuerte Avocado', 'Persea americana', 150],
            ['Grafted Tommy Mango', 'Mangifera indica', 200],
            ['Grafted Apple Mango', 'Mangifera indica', 200],
        ];

        foreach ($plants as $i => [$name, $botanical, $price]) {
            Plant::firstOrCreate(['source_ref' => 'catalogue-2026-11:'.str($name)->slug()], [
                'category_id' => $category,
                'name' => $name,
                'slug' => str($name)->slug(),
                'botanical_name' => $botanical,
                'sold_as' => SoldAs::Seedling,
                'price' => $price,
                'stock_qty' => 0,
                'availability' => Availability::Ready,
                'bulk_min_qty' => 100,
                'description' => 'Grafted, hardened seedling ready for transplanting.',
                'sort_order' => 100 + $i,
            ]);
        }
    }
}
