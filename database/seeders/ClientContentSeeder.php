<?php

namespace Database\Seeders;

use App\Models\GalleryImage;
use App\Models\Setting;
use App\Models\TeamMember;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Details the client sent on 27 Nov 2026: the About text, the five nursery
 * units and their teams, the M-Pesa paybill (from a Wisdom Capital invoice),
 * delivery anywhere in Kenya, and new nursery photos.
 *
 * Runs once and only fills what is still a [placeholder] or a photo we seeded
 * earlier, so nothing the team has edited in the dashboard is overwritten.
 */
class ClientContentSeeder extends Seeder
{
    private const ABOUT = 'Operating from Kitui Central, our commercial propagation nursery turns dependable propagation practices into predictable supply for individual growers, farmer cooperatives, agrovets, developers, NGOs, and county programmes. We focus on quality planting materials, clear stock readiness, and practical growing support, grown in Kitui for farmers, institutions, and landscaping projects.';

    /** setting key => [photo seeded earlier, new photo] (database/seeders/data/site/nursery-N.webp) */
    private const PHOTOS = [
        'image_story_main' => [1, 34],
        'image_story_small' => [8, 36],
        'image_facility_1' => [22, 33],
        'image_facility_2' => [15, 31],
    ];

    /** [photo, caption, gallery category] */
    private const GALLERY = [
        [32, 'Seedling beds in the greenhouse', 'nursery'],
        [36, 'Cypress seedlings in trays', 'plants'],
        [31, 'Under the shade net', 'nursery'],
        [30, 'Palms in grow bags', 'plants'],
        [34, 'Inside the greenhouse', 'nursery'],
        [35, 'Cypress seedlings hardening off', 'plants'],
        [33, 'Greenhouse beds', 'nursery'],
    ];

    /** Nursery units and their teams (client's staff list, 27 Nov 2026). Phone numbers are kept off the public site. */
    private const UNITS = [
        'Kilimanjaro Greenhouse' => ['Jacob Malonza', ['Jackline John']],
        'Everest Nursery' => ['Julius Muthengi Katee', ['Catherine Muli', 'Risper Nzomo']],
        'Elgon Nursery' => ['Jackline Nduku', ['Kamene Kitonga', 'Cynthia Muthengi']],
        'Longonot Greenhouse' => ['David Musyoka', ['Gladys Michael Alfonce']],
        'Mt. Kenya Greenhouse' => ['Vincent Musyoka', ['Jeremiah Malombe', 'Patience Kavutha', 'Dorcas Mwati']],
    ];

    public function run(): bool
    {
        if (! is_file($this->source(34))) {
            return false; // photos not in the release yet: try again next deploy
        }

        $this->fill([
            'about_text' => self::ABOUT,
            'story_title' => 'Grown in Kitui for farmers, institutions and landscaping projects.',
            'story_text' => self::ABOUT,
            'address' => 'Kitui Central, Kitui County',
            'delivery_areas_summary' => 'anywhere in Kenya',
            'facilities_text' => 'Stock is tracked by readiness and hardening status before dispatch, with written quotations, clear delivery terms and planting guidance.',
            'facility_units' => implode("\n", array_keys(self::UNITS)),
            'about_focus_1_title' => 'Fruit propagation',
            'about_focus_1_text' => 'Grafted avocados, mangoes, citrus, berries, nuts, and orchard and specialty fruit varieties selected for true-to-type traits and earlier fruiting.',
            'about_focus_2_title' => 'Vegetable seedlings',
            'about_focus_2_text' => 'High-demand seedlings such as tomato, cabbage and kale for commercial growers, irrigation schemes and repeat supply schedules.',
            'about_focus_3_title' => 'Ornamentals',
            'about_focus_3_text' => 'Lemon cypress, Italian cypress, crotons, decorative shrubs, ornamental flowers, Ashoka and selected palms for landscaping and property projects.',
            'about_focus_4_title' => 'Commercial standards',
            'about_focus_4_text' => 'Stock tracked by readiness and hardening status before dispatch, with written quotations, clear delivery terms and planting guidance.',
        ]);

        // Lipa na M-Pesa paybill on Wisdom Capital's invoices (ABSA, Kitui branch).
        if (blank(setting('till_number'))) {
            Setting::put('till_number', '303030');
            Setting::put('mpesa_account', '2043089423');
        }

        foreach (self::PHOTOS as $key => [$old, $new]) {
            if (in_array(setting($key), [null, '', "site/nursery-{$old}.webp"], true)) {
                Setting::put($key, $this->copy($new));
            }
        }

        $next = (int) GalleryImage::max('sort_order') + 1;
        foreach (self::GALLERY as $i => [$n, $caption, $category]) {
            GalleryImage::firstOrCreate(['path' => $this->copy($n)], [
                'caption' => $caption, 'category' => $category, 'sort_order' => $next + $i,
            ]);
        }

        // The team: replace the unnamed placeholder cards, keep anyone staff added.
        TeamMember::where('name', '[Name]')->delete();
        $sort = (int) TeamMember::max('sort_order');
        foreach (self::UNITS as $unit => [$inCharge, $assistants]) {
            TeamMember::firstOrCreate(['name' => $inCharge, 'role' => "In-charge, {$unit}"], ['sort_order' => ++$sort]);
            foreach ($assistants as $name) {
                TeamMember::firstOrCreate(['name' => $name, 'role' => "Assistant, {$unit}"], ['sort_order' => ++$sort]);
            }
        }

        return true;
    }

    /** Set each value only where the setting is missing, empty or still a [placeholder]. */
    private function fill(array $values): void
    {
        foreach ($values as $key => $value) {
            $current = setting($key);
            if (blank($current) || str_starts_with($current, '[')) {
                Setting::put($key, $value);
            }
        }
    }

    private function source(int $n): string
    {
        return database_path("seeders/data/site/nursery-{$n}.webp");
    }

    private function copy(int $n): string
    {
        $target = "site/nursery-{$n}.webp";
        if (! Storage::disk('public')->exists($target)) {
            Storage::disk('public')->put($target, file_get_contents($this->source($n)));
        }

        return $target;
    }
}
