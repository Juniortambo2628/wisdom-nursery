<?php

namespace Database\Seeders;

use App\Enums\Availability;
use App\Enums\SoldAs;
use App\Models\Category;
use App\Models\Plant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Imports the nursery plants from the Wisdom Capital product export.
 *
 *   database/seeders/data/wisdom-capital-products.csv
 *   database/seeders/data/images/<file>
 *
 * Only rows whose category mentions "nursery" are imported. Idempotent: rows
 * are matched on `source_ref` (the export's id, or a slug of the name) and an
 * existing plant is never overwritten, so prices and stock edited by the team
 * are safe. Returns false (retry next deploy) while the CSV is missing.
 */
class PlantImportSeeder extends Seeder
{
    public const CSV = 'seeders/data/wisdom-capital-products.csv';

    public const IMAGES = 'seeders/data/images';

    /** Accepted header names for each field (lower-cased, trimmed). */
    private const ALIASES = [
        'id' => ['id', 'product_id', 'sku'],
        'name' => ['name', 'title', 'product_name', 'product'],
        'category' => ['category', 'categories', 'product_category', 'department'],
        'subcategory' => ['subcategory', 'sub_category', 'type', 'tags'],
        'price' => ['price', 'regular_price', 'price_kes', 'amount'],
        'description' => ['description', 'short_description', 'details'],
        'image' => ['image', 'images', 'image_url', 'featured_image', 'photo'],
        'botanical_name' => ['botanical_name', 'scientific_name', 'latin_name'],
        'stock' => ['stock', 'stock_qty', 'quantity', 'qty'],
    ];

    public function run(): bool
    {
        $path = database_path(self::CSV);
        if (! is_file($path)) {
            $this->command?->warn('Plant import skipped: '.self::CSV.' not found (will retry next deploy).');

            return false;
        }

        $rows = $this->readCsv($path);
        $categories = Category::pluck('id', 'slug');
        $created = 0;
        $report = [];

        foreach ($rows as $i => $row) {
            if (! Str::contains(Str::lower($row['category'] ?? ''), 'nursery')) {
                continue;
            }
            $name = trim($row['name'] ?? '');
            if ($name === '') {
                continue;
            }

            $ref = 'wc:'.($row['id'] ?: Str::slug($name));
            if (Plant::where('source_ref', $ref)->exists()) {
                continue;
            }

            preg_match('/\d[\d,]*(?:\.\d+)?/', (string) ($row['price'] ?? ''), $m);
            $price = (int) round((float) str_replace(',', '', $m[0] ?? '0'));
            $categorySlug = $this->categoryFor($name.' '.($row['subcategory'] ?? '').' '.($row['category'] ?? ''), $price);

            $plant = Plant::create([
                'category_id' => $categories[$categorySlug],
                'name' => $name,
                'slug' => $this->uniqueSlug($name),
                'botanical_name' => ($row['botanical_name'] ?? null) ?: null,
                'sold_as' => in_array($categorySlug, ['tree-seedlings', 'vegetable-seedlings'], true) ? SoldAs::Seedling : SoldAs::Potted,
                'price' => $price,
                // Stock is not in the export: 0 until the team confirms counts (proposal 06).
                'stock_qty' => (int) ($row['stock'] ?? 0),
                'availability' => Availability::Ready,
                'description' => ($row['description'] ?? null) ?: null,
                'sort_order' => $i,
                'source_ref' => $ref,
            ]);

            $this->attachImage($plant, $row['image'] ?? '');
            $created++;
            $report[] = [$name, $categorySlug, $price];
        }

        $this->command?->table(['Plant', 'Category', 'KES'], $report);
        $this->command?->info("Imported {$created} plants.");

        return true;
    }

    /** @return list<array<string,string>> rows keyed by canonical field name */
    private function readCsv(string $path): array
    {
        $fh = fopen($path, 'r');
        $header = array_map(fn ($h) => Str::snake(Str::lower(trim((string) $h, " \t\n\r\0\x0B\xEF\xBB\xBF"))), fgetcsv($fh) ?: []);

        $map = [];
        foreach (self::ALIASES as $field => $aliases) {
            foreach ($aliases as $alias) {
                $idx = array_search($alias, $header, true);
                if ($idx !== false) {
                    $map[$field] = $idx;
                    break;
                }
            }
        }

        $rows = [];
        while (($line = fgetcsv($fh)) !== false) {
            $row = [];
            foreach ($map as $field => $idx) {
                $row[$field] = trim((string) ($line[$idx] ?? ''));
            }
            $rows[] = $row;
        }
        fclose($fh);

        return $rows;
    }

    private function categoryFor(string $text, int $price): string
    {
        $t = Str::lower($text);

        return match (true) {
            Str::contains($t, 'croton megalocarpus') => 'tree-seedlings', // a tree, not the ornamental croton
            Str::contains($t, ['tomato', 'cabbage', 'kale', 'sukuma', 'spinach', 'onion', 'capsicum', 'vegetable']) => 'vegetable-seedlings',
            Str::contains($t, ['palm', 'cypress', 'croton', 'ashoka', 'ornamental', 'shrub', 'flower']) => 'ornamentals-palms',
            Str::contains($t, ['fruit', 'mango', 'avocado', 'orange', 'lemon', 'pawpaw', 'grafted']) => 'fruit-trees',
            Str::contains($t, ['seedling', 'grevillea', 'neem', 'blue gum', 'eucalyptus', 'pine', 'croton megalocarpus', 'tree']) => 'tree-seedlings',
            Str::contains($t, 'indoor') => 'indoor-plants',
            // Proposal: seedlings sell from KES 30, indoor plants at KES 1,600.
            $price > 0 && $price < 200 => 'tree-seedlings',
            default => 'indoor-plants',
        };
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $n = 2;
        while (Plant::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$n++;
        }

        return $slug;
    }

    private function attachImage(Plant $plant, string $image): void
    {
        $file = basename(trim(explode(',', $image)[0] ?? ''));
        if ($file === '') {
            return;
        }
        $source = database_path(self::IMAGES.'/'.$file);
        if (! is_file($source)) {
            $this->command?->warn("Image not found for {$plant->name}: {$file}");

            return;
        }

        $target = 'plants/'.$plant->slug.'.'.Str::lower(pathinfo($file, PATHINFO_EXTENSION));
        Storage::disk('public')->put($target, file_get_contents($source));
        $plant->images()->create(['path' => $target, 'alt' => $plant->name, 'sort_order' => 0]);
    }
}
