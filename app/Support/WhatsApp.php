<?php

namespace App\Support;

use App\Models\Plant;
use Illuminate\Support\Collection;

/** wa.me links with the message pre-filled. */
class WhatsApp
{
    public static function link(?string $text = null): ?string
    {
        $number = preg_replace('/\D/', '', (string) config('nursery.whatsapp'));
        if ($number === '') {
            return null;
        }

        return 'https://wa.me/'.$number.($text ? '?text='.rawurlencode($text) : '');
    }

    public static function forPlant(Plant $plant, int $qty = 1): ?string
    {
        return self::link("Hello Wisdom Propagation Nursery, I'd like to order {$qty} × {$plant->name} (".ksh($plant->price).' each). '
            .route('plants.show', $plant));
    }

    /** @param Collection<int, object> $lines from Cart::lines() */
    public static function forCart(Collection $lines, int $total): ?string
    {
        $items = $lines->map(fn ($l) => "• {$l->qty} × {$l->label}: ".ksh($l->lineTotal))->implode("\n");

        return self::link("Hello Wisdom Propagation Nursery, I'd like to order:\n{$items}\nTotal: ".ksh($total));
    }
}
