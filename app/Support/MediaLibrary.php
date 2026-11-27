<?php

namespace App\Support;

use App\Models\Category;
use App\Models\GalleryImage;
use App\Models\Partner;
use App\Models\PlantImage;
use App\Models\Post;
use App\Models\Setting;
use App\Models\TeamMember;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

/**
 * Every photo uploaded to the site (the public disk), for the dashboard's Photo library
 * and the "Choose from library" picker, so the same photo is never uploaded twice.
 */
class MediaLibrary
{
    private const EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'avif'];

    /** Folder on the disk => name staff see. */
    public const FOLDERS = [
        'site' => 'Site photos', 'plants' => 'Plants', 'team' => 'Team', 'gallery' => 'Gallery',
        'categories' => 'Categories', 'posts' => 'Grower tips', 'partners' => 'Partners', 'library' => 'Library uploads',
    ];

    /** @return Collection<int, array{path: string, url: string, thumb: string, name: string, folder: string, size: int, modified: int}> */
    public static function images(): Collection
    {
        $disk = Storage::disk('public');

        return collect($disk->allFiles())
            ->filter(fn ($p) => in_array(strtolower(pathinfo($p, PATHINFO_EXTENSION)), self::EXTENSIONS, true))
            ->reject(fn ($p) => str_starts_with($p, 'livewire-tmp/') || preg_match('/-(sm|thumb)\.\w+$/', $p)) // phone and thumbnail copies stay hidden
            ->map(function ($p) use ($disk) {
                $thumb = collect([ImageOptimizer::variant($p, 'thumb'), ImageOptimizer::variant($p, 'sm')])->first(fn ($v) => $disk->exists($v));

                return [
                    'path' => $p,
                    'url' => $disk->url($p),
                    'thumb' => $disk->url($thumb ?? $p),
                    'name' => basename($p),
                    'folder' => str_contains($p, '/') ? strstr($p, '/', true) : '',
                    'size' => $disk->size($p),
                    'modified' => $disk->lastModified($p),
                ];
            })
            ->sortByDesc('modified')
            ->values();
    }

    /** path => where it is used, e.g. ["Photo slot: image_hero", "Plant: Swiss Cheese Plant"]. */
    public static function usage(): array
    {
        $used = [];
        $add = function (?string $path, string $where) use (&$used) {
            if (filled($path)) {
                $used[$path][] = $where;
            }
        };

        foreach (Setting::allValues() as $key => $value) {
            if (is_string($value) && preg_match('#^[a-z-]+/[^\s]+\.(jpe?g|png|webp|gif|avif)$#i', $value)) {
                $add($value, "Site settings: {$key}");
            }
        }
        PlantImage::with('plant:id,name')->get()->each(fn ($i) => $add($i->path, 'Plant: '.($i->plant?->name ?? '?')));
        Category::all()->each(fn ($c) => $add($c->image_path, "Category: {$c->name}"));
        TeamMember::all()->each(fn ($m) => $add($m->photo_path, "Team: {$m->name}"));
        GalleryImage::all()->each(fn ($g) => $add($g->path, 'Gallery: '.($g->caption ?: 'photo')));
        Post::all()->each(fn ($p) => $add($p->image_path, "Grower tip: {$p->title}"));
        Partner::all()->each(fn ($p) => $add($p->logo_path, "Partner: {$p->name}"));

        return $used;
    }
}
