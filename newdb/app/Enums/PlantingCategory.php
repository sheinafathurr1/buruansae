<?php

namespace App\Enums;

enum PlantingCategory: string
{
    case Seed = 'seed';
    case Seedling = 'seedling';
    case Tree = 'tree';

    public function label(): string
    {
        return match ($this) {
            self::Seed => 'Benih',
            self::Seedling => 'Bibit',
            self::Tree => 'Pohon',
        };
    }
}
