<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Images generated from the prompts document (27 Nov 2026), showing the team in the
 * Wisdom Nursery overalls: five grower tips, the five service cards and the Services
 * header, and the five category cards. Ships with its -sm / -thumb copies.
 *
 * Only replaces photos this project seeded; anything uploaded in the dashboard stays.
 */
class GeneratedImagesSeeder extends Seeder
{
    private const POSTS = [
        'caring-for-indoor-plants' => 'tip-indoor-plants',
        'watering-how-much-and-how-often' => 'tip-watering',
        'repotting-without-stress' => 'tip-repotting',
        'how-to-plant-a-tree-seedling' => 'tip-planting-a-tree',
        'choosing-trees-for-your-land' => 'tip-choosing-trees',
    ];

    private const SETTINGS = [
        'image_services_banner' => 'header-services',
        'service_1_image' => 'service-seed',
        'service_2_image' => 'service-cuttings',
        'service_3_image' => 'service-agronomy',
        'service_4_image' => 'service-crop-info',
    ];

    private const CATEGORIES = [
        'fruit-trees' => 'category-fruit-trees',
        'vegetable-seedlings' => 'category-vegetable-seedlings',
        'ornamentals-palms' => 'category-ornamentals',
        'indoor-plants' => 'category-indoor-plants',
        'tree-seedlings' => 'category-tree-seedlings',
    ];

    public function run(): bool
    {
        if (! is_file($this->source('header-services'))) {
            return false;
        }

        foreach (self::POSTS as $slug => $name) {
            $post = Post::where('slug', $slug)->first();
            if ($post && $this->seeded($post->image_path)) {
                $post->update(['image_path' => $this->copy($name)]);
            }
        }

        foreach (self::SETTINGS as $key => $name) {
            if ($this->seeded(setting($key))) {
                Setting::put($key, $this->copy($name));
            }
        }

        foreach (self::CATEGORIES as $slug => $name) {
            $category = Category::where('slug', $slug)->first();
            if ($category && $this->seeded($category->image_path)) {
                $category->update(['image_path' => $this->copy($name)]);
            }
        }

        return true;
    }

    /** Empty, or a photo this project put there (dashboard uploads live in other folders). */
    private function seeded(?string $path): bool
    {
        return blank($path) || (bool) preg_match('#^(site/nursery-\d+|plants/[a-z0-9-]+)\.webp$#', $path);
    }

    private function source(string $name, string $suffix = ''): string
    {
        return database_path("seeders/data/generated/{$name}{$suffix}.webp");
    }

    private function copy(string $name): string
    {
        foreach (['', '-sm', '-thumb'] as $suffix) {
            Storage::disk('public')->put("site/{$name}{$suffix}.webp", file_get_contents($this->source($name, $suffix)));
        }

        return "site/{$name}.webp";
    }
}
