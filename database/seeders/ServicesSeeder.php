<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * The five services on the Our services page (the same five as the client's
 * reference, Longonot Farm). Wording is drawn from the client's About text and
 * is editable in Site settings → Services, as are the photos.
 */
class ServicesSeeder extends Seeder
{
    /** [title, text, photo number (database/seeders/data/site/nursery-N.webp)] */
    private const SERVICES = [
        1 => ['Seed propagation', 'Vegetable, tree and ornamental seedlings raised from seed in clean trays and nursery beds, then hardened in the open before they leave us.', 52],
        2 => ['Cuttings propagation', 'Plants raised from cuttings and grafts of healthy parent plants, including grafted fruit trees selected for true-to-type traits and earlier fruiting.', 35],
        3 => ['Agronomy support', 'Practical growing support and planting guidance for individual growers, farmer cooperatives, institutions and county programmes.', 49],
        4 => ['Crop information', 'Clear stock readiness, and plain guides on planting, watering and caring for young plants and trees.', 41],
        5 => ['Ornamentals & garden', 'Lemon cypress, Italian cypress, crotons, decorative shrubs, ornamental flowers, Ashoka and selected palms for landscaping and property projects.', 66],
    ];

    public function run(): bool
    {
        if (! is_file(database_path('seeders/data/site/nursery-66.webp'))) {
            return false; // photos not in the release yet: try again next deploy
        }

        foreach (self::SERVICES as $i => [$title, $text, $photo]) {
            Setting::firstOrCreate(['key' => "service_{$i}_title"], ['value' => $title]);
            Setting::firstOrCreate(['key' => "service_{$i}_text"], ['value' => $text]);
            if (blank(setting("service_{$i}_image"))) {
                $target = "site/nursery-{$photo}.webp";
                if (! Storage::disk('public')->exists($target)) {
                    Storage::disk('public')->put($target, file_get_contents(database_path("seeders/data/site/nursery-{$photo}.webp")));
                }
                Setting::put("service_{$i}_image", $target);
            }
        }

        return true;
    }
}
