<?php

namespace App\Enums;

enum SoldAs: string
{
    case Potted = 'potted';
    case PerPlant = 'per_plant';
    case Seedling = 'seedling';
    case Tray = 'tray';

    public function label(): string
    {
        return match ($this) {
            self::Potted => 'Potted',
            self::PerPlant => 'Per plant',
            self::Seedling => 'Seedling',
            self::Tray => 'Tray',
        };
    }
}
