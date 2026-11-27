<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

/** Once per site: compress the photos already uploaded (new uploads are compressed as they arrive). */
class OptimizeImagesSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->runningUnitTests()) {
            return; // covered by its own test; compressing every seeded photo would slow each test by seconds
        }
        Artisan::call('nursery:optimize-images');
        $this->command?->line(trim(Artisan::output()));
    }
}
