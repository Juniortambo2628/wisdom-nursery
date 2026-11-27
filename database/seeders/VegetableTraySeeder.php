<?php

namespace Database\Seeders;

use App\Enums\Availability;
use App\Enums\SoldAs;
use App\Models\Category;
use App\Models\Plant;
use Illuminate\Database\Seeder;

/**
 * Vegetable seedling trays and prices the client sent on 27 Nov 2026. Photos
 * follow from a photographer. Stock is 0 until the team enters real counts in
 * the dashboard (Plants → "In stock"), as with the grafted fruit trees.
 */
class VegetableTraySeeder extends Seeder
{
    public function run(): void
    {
        $category = Category::where('slug', 'vegetable-seedlings')->value('id');
        if (! $category) {
            return;
        }

        // [name, botanical name, price per tray, description]
        $trays = [
            ['Tomato Ansal F1', 'Solanum lycopersicum', 1900, 'Tray of 442 seedlings.'],
            ['Tomato Bigrock F1', 'Solanum lycopersicum', 2000, null],
            ['Tomato Rafano', 'Solanum lycopersicum', 1800, null],
            ['Tomato Ranger', 'Solanum lycopersicum', 1800, null],
            ['Tomato Safa', 'Solanum lycopersicum', 1500, null],
            ['Tomato Kilele', 'Solanum lycopersicum', 1600, null],
            ['Hoho Superbell', 'Capsicum annuum', 1200, 'Sweet pepper (hoho) seedlings.'],
            ['Cabbage', 'Brassica oleracea', 800, null],
            ['Collards', 'Brassica oleracea', 800, null],
            ['Spinach', null, 800, null],
            ['Sukuma Matumbo', 'Brassica oleracea', 800, 'Kale (sukuma wiki) seedlings.'],
            ['Melon Sukari F1', null, 1200, 'Tray of 210 seedlings.'],
            ['Onion', 'Allium cepa', 1600, 'Tray of 442 holes, about 5 seedlings in each hole.'],
        ];

        foreach ($trays as $i => [$name, $botanical, $price, $description]) {
            Plant::firstOrCreate(['source_ref' => 'catalogue-2026-11-27:'.str($name)->slug()], [
                'category_id' => $category,
                'name' => $name,
                'slug' => $this->uniqueSlug($name),
                'botanical_name' => $botanical,
                'sold_as' => SoldAs::Tray,
                'price' => $price,
                'stock_qty' => 0,
                'availability' => Availability::Ready,
                'description' => trim(($description ? $description.' ' : '').'Priced per tray.'),
                'sort_order' => 200 + $i,
            ]);
        }
    }

    private function uniqueSlug(string $name): string
    {
        $slug = (string) str($name)->slug();

        return Plant::where('slug', $slug)->exists() ? $slug.'-tray' : $slug;
    }
}
