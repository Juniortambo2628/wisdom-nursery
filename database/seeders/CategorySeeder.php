<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        // Aligned to the client's divisions (Kitui sales strategy, 24 Nov 2026).
        $categories = [
            ['slug' => 'fruit-trees', 'name' => 'Fruit trees', 'summary' => 'Grafted mango, avocado and orange, ready for transplanting.'],
            ['slug' => 'vegetable-seedlings', 'name' => 'Vegetable seedlings', 'summary' => 'Tomato, cabbage, kale and more, for farms and kitchen gardens.'],
            ['slug' => 'ornamentals-palms', 'name' => 'Ornamentals & palms', 'summary' => 'Cypress, crotons, Ashoka and palms for compounds and landscaping.'],
            ['slug' => 'indoor-plants', 'name' => 'Indoor plants', 'summary' => 'Swiss cheese plant, bird of paradise, alocasia and more.'],
            ['slug' => 'tree-seedlings', 'name' => 'Tree seedlings', 'summary' => 'Grevillea, neem, blue gum and more, by the tray.'],
        ];

        foreach ($categories as $i => $c) {
            Category::firstOrCreate(['slug' => $c['slug']], [
                'name' => $c['name'],
                'summary' => $c['summary'],
                'sort_order' => $i,
            ]);
        }
    }
}
