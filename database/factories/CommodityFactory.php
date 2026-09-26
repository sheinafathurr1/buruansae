<?php

namespace Database\Factories;

use App\Models\Commodity;
use App\Models\Sector;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Commodity>
 */
class CommodityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'sector_id' => Sector::factory(),
            'name' => strtoupper(fake()->unique()->bothify('Komoditas ????##')),
            'growing_days' => fake()->numberBetween(20, 120),
        ];
    }

    /** Pakai sektor hasil SectorSeeder, mis. ->inSector('SAYUR'). */
    public function inSector(string $code): static
    {
        return $this->state(fn () => ['sector_id' => Sector::query()->where('code', $code)->value('id')]);
    }
}
