<?php

namespace Database\Seeders;

use App\Models\TeamMember;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Team portraits from the client (27 Nov 2026), and the people in them who were
 * not on the earlier staff list. Their units were not given, so they are listed
 * as "Nursery team" until staff set a role in Content → Team.
 *
 * Only sets a photo where a member has none, so dashboard uploads are kept.
 */
class TeamPhotoSeeder extends Seeder
{
    /** photo file (database/seeders/data/team/*.webp) => names it may already be listed under */
    private const PHOTOS = [
        'catherine-muli' => ['Catherine Muli'],
        'cynthia-muthengi' => ['Cynthia Muthengi'],
        'david-musyoka' => ['David Musyoka'],
        'dorcas-mwati' => ['Dorcas Mwati'],
        'gladys-alfonce' => ['Gladys Michael Alfonce', 'Gladys Alfonce'],
        'jackline-john' => ['Jackline John'],
        'jacob-paul-malonza' => ['Jacob Malonza', 'Jacob Paul Malonza'],
        'jeremiah-malombe' => ['Jeremiah Malombe'],
        'julius-muthengi' => ['Julius Muthengi Katee', 'Julius Muthengi'],
        'kamene-kitonga' => ['Kamene Kitonga'],
        'patience-kavutha' => ['Patience Kavutha'],
        'risper-nzomo' => ['Risper Nzomo'],
        'vincent-musyoka' => ['Vincent Musyoka'],
        // Not on the staff list sent earlier:
        'agnes-ndanu' => ['Agnes Ndanu'],
        'edna-john' => ['Edna John'],
        'irene-mwende' => ['Irene Mwende'],
        'irene-ndinga' => ['Irene Ndinga'],
        'jackline-kingalwa' => ['Jackline Kingalwa'],
        'josephine-malu' => ['Josephine Malu'],
    ];

    public function run(): bool
    {
        if (! is_file(database_path('seeders/data/team/agnes-ndanu.webp'))) {
            return false; // photos not in the release yet: try again next deploy
        }

        $sort = (int) TeamMember::max('sort_order');
        foreach (self::PHOTOS as $file => $names) {
            $member = TeamMember::whereIn('name', $names)->first()
                ?? TeamMember::create(['name' => $names[0], 'role' => 'Nursery team', 'sort_order' => ++$sort]);

            if (blank($member->photo_path)) {
                $path = "team/{$file}.webp";
                Storage::disk('public')->put($path, file_get_contents(database_path("seeders/data/team/{$file}.webp")));
                $member->update(['photo_path' => $path]);
            }
        }

        return true;
    }
}
