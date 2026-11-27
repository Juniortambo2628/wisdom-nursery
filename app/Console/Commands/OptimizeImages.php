<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Support\ImageOptimizer;
use App\Support\MediaLibrary;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/** Compress every photo already on the site and add phone/thumbnail copies; updates references when a file becomes .webp. */
class OptimizeImages extends Command
{
    protected $signature = 'nursery:optimize-images';

    protected $description = 'Resize and convert uploaded photos to WebP, with -sm and -thumb copies';

    /** table => column holding a public-disk photo path */
    private const COLUMNS = [
        'plant_images' => 'path', 'categories' => 'image_path', 'team_members' => 'photo_path',
        'gallery_images' => 'path', 'posts' => 'image_path', 'partners' => 'logo_path',
    ];

    public function handle(): int
    {
        if (! ImageOptimizer::supported()) {
            $this->warn('PHP GD with WebP is not available here; photos left as they are.');

            return self::SUCCESS;
        }

        $before = $after = 0;
        foreach (MediaLibrary::images() as $img) {
            $before += $img['size'];
            $new = ImageOptimizer::process($img['path']);
            $after += \Illuminate\Support\Facades\Storage::disk('public')->size($new);
            if ($new !== $img['path']) {
                $this->renameReferences($img['path'], $new);
            }
        }

        $this->info(sprintf('Photos: %s MB → %s MB, with phone and thumbnail copies.', round($before / 1048576, 1), round($after / 1048576, 1)));

        return self::SUCCESS;
    }

    private function renameReferences(string $old, string $new): void
    {
        Setting::where('value', $old)->update(['value' => $new]);
        Cache::forget('settings.all');
        foreach (self::COLUMNS as $table => $column) {
            DB::table($table)->where($column, $old)->update([$column => $new]);
        }
    }
}
