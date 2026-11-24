<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * Runs on every deploy (deploy/remote.sh). Each content seeder runs ONCE per
 * database and is then recorded in `settings` as "seeded:<Class>", so later
 * deploys never overwrite what the team has edited in the dashboard, while new
 * seeders added later still run on the next deploy.
 *
 * A seeder that cannot run yet (e.g. the plant import before its CSV is in the
 * repo) returns false from run() and is retried on the next deploy.
 */
class DatabaseSeeder extends Seeder
{
    /** @var list<class-string<Seeder>> */
    private array $once = [
        CategorySeeder::class,
        SettingsSeeder::class,
        PlantImportSeeder::class,
        FruitTreeSeeder::class,
        PostSeeder::class,
        TeamSeeder::class,
        GallerySeeder::class,
        SitePhotosSeeder::class,
        ResponsivePhotosSeeder::class,
        TestimonialSeeder::class,
    ];

    public function run(): void
    {
        foreach ($this->once as $class) {
            $key = 'seeded:'.class_basename($class);
            if (Setting::get($key)) {
                continue;
            }

            $done = $this->resolve($class)->run();
            if ($done !== false) {
                Setting::put($key, now()->toIso8601String());
                $this->command?->info("Seeded {$class}");
            }
        }
    }
}
