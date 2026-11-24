<?php

namespace App\Enums;

enum Availability: string
{
    case Ready = 'ready';
    case ReadyOn = 'ready_on';
    case OutOfStock = 'out_of_stock';

    public function label(): string
    {
        return match ($this) {
            self::Ready => 'Ready now',
            self::ReadyOn => 'Coming soon',
            self::OutOfStock => 'Out of stock',
        };
    }
}
