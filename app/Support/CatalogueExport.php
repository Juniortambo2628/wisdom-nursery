<?php

namespace App\Support;

use App\Models\Plant;

/**
 * Plant list as a CSV for the WhatsApp Business catalogue (Meta Commerce
 * Manager → Catalogue → Data sources → Data feed / file upload). Proposal 08:
 * "WhatsApp Business catalogue with the same plants and prices".
 */
class CatalogueExport
{
    public const COLUMNS = ['id', 'title', 'description', 'availability', 'condition', 'price', 'link', 'image_link', 'brand'];

    public static function csv(): string
    {
        $out = fopen('php://temp', 'r+');
        fputcsv($out, self::COLUMNS);

        Plant::active()->with('primaryImage', 'category')->ordered()->get()
            ->filter(fn (Plant $p) => $p->primaryImage) // Meta requires an image
            ->each(function (Plant $p) use ($out) {
                fputcsv($out, [
                    $p->slug,
                    mb_substr($p->name, 0, 150),
                    mb_substr($p->description ?: "{$p->name} from Wisdom Nursery ({$p->category->name}).", 0, 5000),
                    $p->isBuyable() ? ($p->isComingSoon() ? 'preorder' : 'in stock') : 'out of stock',
                    'new',
                    $p->price.'.00 KES',
                    route('plants.show', $p),
                    $p->primaryImage->url(),
                    'Wisdom Nursery',
                ]);
            });

        rewind($out);

        return stream_get_contents($out);
    }
}
