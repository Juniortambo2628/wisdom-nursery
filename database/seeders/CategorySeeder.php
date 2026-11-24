<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['slug' => 'indoor-plants', 'name' => 'Indoor plants'],
            ['slug' => 'palms', 'name' => 'Palms'],
            ['slug' => 'outdoor-fruit-trees', 'name' => 'Outdoor & fruit trees'],
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
