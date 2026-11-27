<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * Narrative shift requested by the client's management (28 Nov 2026): from "we are
 * located in Kitui" to why plants raised in Kitui's semi-arid conditions are tougher
 * and establish better than plants raised elsewhere.
 *
 * Each value is only written where the setting is empty, still a [placeholder], or
 * still the exact text this project seeded before, so staff edits are kept.
 */
class NarrativeSeeder extends Seeder
{
    private const OLD_ABOUT = 'Operating from Kitui Central, our commercial propagation nursery turns dependable propagation practices into predictable supply for individual growers, farmer cooperatives, agrovets, developers, NGOs, and county programmes. We focus on quality planting materials, clear stock readiness, and practical growing support, grown in Kitui for farmers, institutions, and landscaping projects.';

    private const ABOUT = 'Our commercial propagation nursery raises plants in the semi-arid conditions of Kitui, Kenya, where strong sun, heat and long dry spells toughen every seedling from the start. The result is stock with deeper roots and hardier growth that establishes faster and survives better after planting, for individual growers, farmer cooperatives, agrovets, developers, NGOs and county programmes. We back it with quality planting materials, clear stock readiness and practical growing support.';

    /** key => [new value, earlier seeded value it may replace] */
    private function values(): array
    {
        return [
            'about_text' => [self::ABOUT, self::OLD_ABOUT],
            'story_text' => [self::ABOUT, self::OLD_ABOUT],
            'story_title' => ['Toughened in Kitui, ready for your land.', 'Grown in Kitui for farmers, institutions and landscaping projects.'],
            'facilities_text' => [
                'Open beds and shade-net houses let us harden every plant under full Kitui sun before dispatch. Stock is tracked by readiness and hardening status, with written quotations, clear delivery terms and planting guidance.',
                'Stock is tracked by readiness and hardening status before dispatch, with written quotations, clear delivery terms and planting guidance.',
            ],
            'how_we_grow_1' => ['Seed and cuttings from healthy parent plants, sown in clean trays and nursery beds.', null],
            'how_we_grow_2' => ['Seedlings are watered and shaded while they sprout and form strong first roots.', null],
            'how_we_grow_3' => ['Young plants move into the open to toughen under Kitui’s strong sun, heat and dry air before they leave us.', null],
            'how_we_grow_4' => ['Plants are checked, packed and collected or delivered, ready to establish quickly after planting.', null],
            'service_1_text' => [
                'Vegetable, tree and ornamental seedlings raised from seed in clean trays and nursery beds, then hardened in the open under full Kitui sun so they transplant with less shock.',
                'Vegetable, tree and ornamental seedlings raised from seed in clean trays and nursery beds, then hardened in the open before they leave us.',
            ],
            'advantage_eyebrow' => ['Why Kitui-grown', null],
            'advantage_title' => ['Raised tough in semi-arid Kitui.', null],
            'advantage_lead' => ['Strong sun, heat, dry air and long dry spells are hard on young plants. We raise and harden our stock in exactly those conditions, so what leaves our nursery is already used to them and settles in faster wherever you plant it.', null],
            'advantage_1_title' => ['Deeper, denser roots', null],
            'advantage_1_text' => ['Dry spells push roots to grow deep and fibrous, so plants find water and anchor quickly after transplanting.', null],
            'advantage_2_title' => ['Tougher leaves and stems', null],
            'advantage_2_text' => ['Strong sun and heat build thicker leaves and sturdier stems that cope with direct sun, wind and the journey to your site.', null],
            'advantage_3_title' => ['Ready for hard conditions', null],
            'advantage_3_text' => ['Plants hardened in heat and low humidity handle a hot site, a dry season or a missed watering better than plants raised in mild, damp nurseries.', null],
            'advantage_4_title' => ['Clean, vigorous stock', null],
            'advantage_4_text' => ['Dry air means less of the damp-loving fungal disease that soft, humid nurseries contend with, so plants leave us clean and growing strongly.', null],
            'advantage_closing' => ['If it thrives in Kitui, it is ready for your land.', null],
        ];
    }

    public function run(): void
    {
        foreach ($this->values() as $key => [$new, $old]) {
            $current = setting($key);
            if (blank($current) || str_starts_with($current, '[') || ($old !== null && $current === $old)) {
                Setting::put($key, $new);
            }
        }
    }
}
