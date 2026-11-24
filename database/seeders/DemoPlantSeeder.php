<?php

namespace Database\Seeders;

use App\Enums\Availability;
use App\Enums\SoldAs;
use App\Models\Category;
use App\Models\Plant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * LOCAL PREVIEW ONLY: the example plants from the concepts deck, so pages can
 * be built and checked before the real plant list is imported. Not called by
 * DatabaseSeeder, so the deploy never runs it.
 *
 *   php artisan db:seed --class=DemoPlantSeeder
 */
class DemoPlantSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command?->error('Demo plants are for local preview only.');

            return;
        }

        $c = Category::pluck('id', 'slug');
        $rows = [
            ['Swiss Cheese Plant', 'Monstera deliciosa', 'indoor-plants', SoldAs::Potted, 1600, 25, true, ['set_size' => 5, 'set_price' => 7500, 'bulk_min_qty' => 20, 'light' => 'Bright, indirect', 'water' => 'Once a week', 'pot_size' => '[X] cm', 'grows_to' => '[X] m indoors']],
            ['Bird of Paradise', 'Strelitzia reginae', 'indoor-plants', SoldAs::Potted, 1600, 12, true, []],
            ['Alocasia', null, 'indoor-plants', SoldAs::Potted, 1600, 8, false, []],
            ['Zebra Plant', null, 'indoor-plants', SoldAs::Potted, 1600, 0, false, ['availability' => Availability::OutOfStock]],
            ['Peace Lily', 'Spathiphyllum', 'indoor-plants', SoldAs::Potted, 1600, 10, false, []],
            ['Golden Palm', 'Dypsis lutescens', 'ornamentals-palms', SoldAs::PerPlant, 950, 30, true, []],
            ['Christmas Palm', null, 'ornamentals-palms', SoldAs::PerPlant, 950, 15, false, []],
            ['Ashoka Tree', null, 'ornamentals-palms', SoldAs::PerPlant, 750, 40, false, []],
            ['Grafted Mango', null, 'fruit-trees', SoldAs::Seedling, 300, 200, false, []],
            ['Lemon Tree', null, 'fruit-trees', SoldAs::Seedling, 300, 50, false, ['availability' => Availability::ReadyOn, 'ready_on' => now()->addWeeks(3)]],
            ['Kale (Sukuma) Seedlings', null, 'vegetable-seedlings', SoldAs::Tray, 250, 100, false, []],
            ['Grevillea Robusta', 'Grevillea robusta', 'tree-seedlings', SoldAs::Seedling, 30, 5000, true, ['bulk_min_qty' => 100]],
            ['Neem Tree', 'Azadirachta indica', 'tree-seedlings', SoldAs::Seedling, 30, 3000, false, ['bulk_min_qty' => 100]],
        ];

        foreach ($rows as $i => [$name, $botanical, $cat, $soldAs, $price, $stock, $featured, $extra]) {
            Plant::updateOrCreate(['slug' => Str::slug($name)], array_merge([
                'category_id' => $c[$cat],
                'name' => $name,
                'botanical_name' => $botanical,
                'sold_as' => $soldAs,
                'price' => $price,
                'stock_qty' => $stock,
                'availability' => Availability::Ready,
                'description' => '[Two lines on the plant: how big it grows, light and water needs, why customers love it.]',
                'is_featured' => $featured,
                'sort_order' => $i,
            ], $extra));
        }
    }
}
