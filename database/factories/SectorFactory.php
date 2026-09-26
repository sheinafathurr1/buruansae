<?php

namespace Database\Factories;

use App\Models\Sector;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sector>
 */
class SectorFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->word();

        return [
            'code' => strtoupper($name),
            'name' => ucfirst($name),
            'harvest_unit' => 'kg',
        ];
    }
}
