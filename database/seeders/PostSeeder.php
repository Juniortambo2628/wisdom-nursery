<?php

namespace Database\Seeders;

use App\Models\Post;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * The six launch guides (proposal 08), seeded as DRAFTS. Titles are working
 * titles; Kevin supplies the final copy.
 */
class PostSeeder extends Seeder
{
    public function run(): void
    {
        $guides = [
            ['[Working title] Caring for indoor plants', 'Plant care'],
            ['[Working title] Watering: how much and how often', 'Plant care'],
            ['[Working title] Repotting without stress', 'Plant care'],
            ['[Working title] How to plant a tree seedling', 'Tree planting'],
            ['[Working title] Choosing trees for your land', 'Tree planting'],
            ['[Working title] Caring for young trees in the first year', 'Tree planting'],
        ];

        foreach ($guides as [$title, $category]) {
            $slug = Str::slug(Str::after($title, '] '));
            Post::firstOrCreate(['slug' => $slug], [
                'title' => $title,
                'category' => $category,
                'excerpt' => '[One-sentence summary]',
                'body' => "<p>[Guide copy to come from Kevin.]</p>",
                'published_at' => null,
            ]);
        }
    }
}
