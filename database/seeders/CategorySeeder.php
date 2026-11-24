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
            ['slug' => 'fruit-trees', 'name' => 'Fruit trees'],
            ['slug' => 'vegetable-seedlings', 'name' => 'Vegetable seedlings'],
            ['slug' => 'ornamentals-palms', 'name' => 'Ornamentals & palms'],
            ['slug' => 'indoor-plants', 'name' => 'Indoor plants'],
            ['slug' => 'tree-seedlings', 'name' => 'Tree seedlings'],
        ];

        foreach ($categories as $i => $c) {
            Category::firstOrCreate(['slug' => $c['slug']], [
                'name' => $c['name'],
                'summary' => '['.$c['name'].': one-line description]',
                'sort_order' => $i,
            ]);
        }
    }
}
