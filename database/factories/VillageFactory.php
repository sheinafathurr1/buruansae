<?php

namespace Database\Factories;

use App\Models\District;
use App\Models\Village;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Village>
 */
class VillageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'district_id' => District::factory(),
            'name' => strtoupper(fake()->unique()->bothify('Kelurahan ????##')),
            // Di dalam batas Kota Bandung.
            'latitude' => fake()->randomFloat(7, -6.96, -6.86),
            'longitude' => fake()->randomFloat(7, 107.56, 107.72),
        ];
    }

    public function withoutCoordinates(): static
    {
        return $this->state(fn () => ['latitude' => null, 'longitude' => null]);
    }
}
