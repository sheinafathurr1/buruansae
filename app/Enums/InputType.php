<?php

namespace App\Enums;

enum InputType: string
{
    case Fertilizer = 'fertilizer';
    case Feed = 'feed';

    public function label(): string
    {
        return match ($this) {
            self::Fertilizer => 'Pupuk',
            self::Feed => 'Pakan',
        };
    }
}
