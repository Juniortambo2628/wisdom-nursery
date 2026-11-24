<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * Site copy and contact details. Values in [square brackets] are placeholders
 * from the concepts deck, to be replaced with the client's real details in the
 * dashboard (Settings) or by a later seeder. Never invent facts here.
 */
class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'since_year' => '[Year]',
            'site_tagline' => 'Every plant gets a strong start',
            'hero_intro' => '[Hero intro: one or two sentences about Wisdom Nursery]',
            'story_title' => '[Our story heading]',
            'story_text' => '[Our story: who we are, where we grow, why we started. Part of the Wisdom Capital family alongside Wisdom Chicken, Wisdom Feeds and Uki Honey.]',
            'stat_1_value' => '[00]', 'stat_1_label' => '[Stat label]',
            'stat_2_value' => '[00]', 'stat_2_label' => '[Stat label]',
            'stat_3_value' => '[00]', 'stat_3_label' => '[Stat label]',
            'stat_4_value' => '[00]', 'stat_4_label' => '[Stat label]',
            'how_we_grow_1' => '[Step 1: e.g. seed selection]',
            'how_we_grow_2' => '[Step 2]',
            'how_we_grow_3' => '[Step 3]',
            'how_we_grow_4' => '[Step 4]',
            'about_text' => '[Short story: when and why the nursery started, where it grows, and what the team cares about.]',
            'facility_1' => '[Facility 1]',
            'facility_2' => '[Facility 2]',
            'facility_certification' => '[Certification, if any]',
            'facilities_text' => '[Facilities: greenhouses, shade nets, water source]',
            'address' => '[Nursery address]',
            'directions' => '[How to find us]',
            'map_embed_url' => '',
            'map_link' => '',
            'latitude' => '',
            'longitude' => '',
            'hours' => '[Mon–Sat 8:00am – 5:00pm]',
            'phone' => '[+254 7XX XXX XXX]',
            'email' => '[orders@…]',
            'till_number' => '', // shown in the "No prompt? Pay to Till …" fallback
            'delivery_areas_summary' => '[areas]',
            'care_text' => '[Potting, watering, feeding and common problems.]',
            'delivery_text' => '[Delivery areas, fees and how long plants keep after collection.]',
            'agronomist_hours' => '[days & hours]',
            'collection_note' => '[Collect from the nursery during opening hours]',
            'social_facebook' => '',
            'social_instagram' => '',
            'social_tiktok' => '',
        ];

        foreach ($defaults as $key => $value) {
            Setting::firstOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
