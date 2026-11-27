<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * The client's own approved wording (Simeon Malonza, via Aldo, 28 Nov 2026: "we use these
 * messages exactly"), from their Wisdom Nursery brief. Replaces the climate-hardening
 * narrative. Writes a value only where the setting is empty, a [placeholder], or text this
 * project seeded before, so anything staff edited in the dashboard stays.
 */
class ClientCopySeeder extends Seeder
{
    private const P1 = 'Located in the warm, fertile heart of Kitui County, Wisdom Nursery is a specialised horticultural propagation centre dedicated to producing quality ornamental trees, decorative plants, grafted fruit trees and vegetable seedlings.';

    private const P2 = 'Kitui’s warm climate provides favourable conditions for propagating a wide range of tropical and subtropical plants, allowing us to maintain production throughout the year with carefully managed irrigation and modern nursery practices.';

    public function run(): void
    {
        $values = [
            'site_tagline' => 'Growing Green. Cultivating Prosperity.',
            'hero_pill' => 'Quality Ornamental Plants, Grafted Fruit Trees and Horticultural Seedlings',
            'hero_intro' => self::P1,
            'about_text' => self::P1.' '.self::P2,
            'story_title' => 'Our location gives us a distinctive advantage.',
            'story_text' => self::P2,
            'advantage_eyebrow' => '',
            'advantage_title' => 'Why Choose Wisdom Nursery?',
            'advantage_lead' => '',
            'advantage_closing' => '',
            'advantage_1_title' => 'Climate-Suited Plant Production',
            'advantage_1_text' => 'Our nursery specialises in ornamental and fruit-tree varieties suited to warm environments. From elegant cypress trees and decorative palms to vibrant crotons, bougainvillea and other landscaping plants, we provide planting solutions suited to Kenya’s diverse climatic conditions.',
            'advantage_2_title' => 'Quality Grafted Fruit Trees',
            'advantage_2_text' => 'We propagate selected grafted fruit varieties, including avocados, mangoes, oranges, lemons and other citrus fruits. Our focus is on quality planting material that gives farmers and orchard developers an opportunity to establish productive fruit enterprises.',
            'advantage_3_title' => 'A Wide Range of Ornamental Plants',
            'advantage_3_text' => 'Whether you are landscaping a home, commercial property, hotel, school, hospital or public institution, we offer a diverse selection of ornamental trees, flowering plants, decorative bushes and ground covers, including Ashoka, Eugenia, palms, cypress, spider grass, China grass and bougainvillea.',
            'advantage_4_title' => 'Reliable, Year-Round Supply',
            'advantage_4_text' => 'Our warm-climate location, supported by irrigation and controlled propagation facilities, enables us to maintain production throughout the year. This allows us to serve farmers, landscapers, developers, institutions and individual customers whenever they need quality seedlings.',
            'advantage_5_title' => 'Serving Kenya and Beyond',
            'advantage_5_text' => 'Strategically located in Kitui, Wisdom Nursery is positioned to serve customers across Eastern Kenya, Nairobi, Central Kenya and other parts of the country. We aim to make quality ornamental and fruit-tree planting material accessible to both commercial and small-scale customers.',
            'commitment_title' => 'Our Commitment',
            'commitment_text' => "At Wisdom Nursery, we believe that every plant represents an investment in beauty, sustainability and economic opportunity. We are committed to quality propagation, a diverse product range, dependable supply and customer satisfaction.\n\nWhether you are creating a beautiful landscape, establishing a commercial orchard, improving your farm or transforming your home environment, Wisdom Nursery is your trusted partner in growing a greener future.",
            'commitment_closing' => 'WISDOM NURSERY – Growing Today for a Greener Tomorrow.',
            // Our earlier hardening wording, back to neutral (not part of the client's brief).
            'how_we_grow_3' => 'Young plants are moved into the open and hardened before they leave us.',
            'how_we_grow_4' => 'Plants are checked, packed and collected or delivered, ready to plant.',
            'facilities_text' => 'Irrigation and controlled propagation facilities let us maintain production throughout the year. Stock is tracked by readiness and hardening status, with written quotations, clear delivery terms and planting guidance.',
            'service_1_text' => 'Vegetable, tree and ornamental seedlings raised from seed in clean trays and nursery beds, then hardened in the open before they leave us.',
        ];

        $earlier = $this->earlierValues();
        foreach ($values as $key => $new) {
            $current = setting($key);
            if ($current === null || $current === '' || str_starts_with($current, '[') || in_array($current, $earlier[$key] ?? [], true)) {
                Setting::put($key, $new);
            }
        }
    }

    /** key => every value this project seeded for it before. */
    private function earlierValues(): array
    {
        $earlier = [];
        foreach ((new NarrativeSeeder)->values() as $key => [$new, $old]) {
            $earlier[$key] = [$new, ...$old];
        }
        $earlier['site_tagline'] = ['Every plant gets a strong start'];
        $earlier['about_text'][] = 'Operating from Kitui Central, our commercial propagation nursery turns dependable propagation practices into predictable supply for individual growers, farmer cooperatives, agrovets, developers, NGOs, and county programmes. We focus on quality planting materials, clear stock readiness, and practical growing support, grown in Kitui for farmers, institutions, and landscaping projects.';
        $earlier['story_text'][] = $earlier['about_text'][count($earlier['about_text']) - 1];
        $earlier['how_we_grow_3'][] = 'Young plants move into the open to toughen under Kitui’s strong sun, heat and dry air before they leave us.';

        return $earlier;
    }
}
