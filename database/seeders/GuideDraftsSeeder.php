<?php

namespace Database\Seeders;

use App\Models\Post;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Draft copy for the six launch guides (proposal 08), from
 * database/seeders/data/guides/<slug>.html. Fills only guides still holding
 * the placeholder text, and leaves them as DRAFTS: the nursery's agronomist
 * reviews them and staff publish with "Publish on" in Content → Grower tips.
 */
class GuideDraftsSeeder extends Seeder
{
    /** slug => [title, excerpt, photo on the public disk] */
    private const GUIDES = [
        'caring-for-indoor-plants' => ['Caring for indoor plants', 'Light, water, pots and feeding: how to keep indoor plants healthy for years.', 'plants/swiss-cheese-plant.webp'],
        'watering-how-much-and-how-often' => ['Watering: how much and how often', 'Simple checks that work better than any watering timetable, indoors and out.', 'site/nursery-26.webp'],
        'repotting-without-stress' => ['Repotting without stress', 'When a plant needs a bigger pot, and how to move it without setting it back.', 'site/nursery-8.webp'],
        'how-to-plant-a-tree-seedling' => ['How to plant a tree seedling', 'The planting hole, the bag, the graft joint and the first watering: get the start right.', 'site/nursery-28.webp'],
        'choosing-trees-for-your-land' => ['Choosing trees for your land', 'Shade, timber, fruit or a neat compound: match the tree to the job and the land.', 'site/nursery-24.webp'],
        'caring-for-young-trees-in-the-first-year' => ['Caring for young trees in the first year', 'Water, mulch, protection and first care for grafted fruit trees until the next rains.', 'site/nursery-29.webp'],
    ];

    public function run(): bool
    {
        if (! is_dir(database_path('seeders/data/guides'))) {
            return false;
        }

        foreach (self::GUIDES as $slug => [$title, $excerpt, $photo]) {
            $file = database_path("seeders/data/guides/{$slug}.html");
            $post = Post::where('slug', $slug)->first();
            if (! is_file($file) || ! $post || ! str_contains((string) $post->body, '[Guide copy')) {
                continue; // already written or edited by staff
            }

            $post->update([
                'title' => $title,
                'excerpt' => $excerpt,
                'body' => file_get_contents($file),
                'image_path' => Storage::disk('public')->exists($photo) ? $photo : $post->image_path,
                // published_at stays null: drafts until reviewed
            ]);
        }

        return true;
    }
}
