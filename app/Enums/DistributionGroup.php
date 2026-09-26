<?php

namespace App\Enums;

/** Pengelompokan kategori penerima untuk tampilan distribusi hasil. */
enum DistributionGroup: string
{
    case SelfConsumption = 'self';
    case Shared = 'shared';
    case Sold = 'sold';

    public static function fromCategoryCode(string $code): self
    {
        return match ($code) {
            'KP' => self::SelfConsumption,
            'DIJUAL' => self::Sold,
            default => self::Shared,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::SelfConsumption => 'Konsumsi Pribadi',
            self::Shared => 'Dibagikan',
            self::Sold => 'Dijual',
        };
    }
}
