<?php

namespace Database\Factories;

use App\Models\FarmerGroup;
use App\Models\Village;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FarmerGroup>
 */
class FarmerGroupFactory extends Factory
{
    public function definition(): array
    {
        return [
            'village_id' => Village::factory(),
            'rw' => fake()->numberBetween(1, 20),
            'name' => fake()->unique()->bothify('Buruan SAE ????## RW ##'),
            'leader_name' => fake()->name(),
            'extension_officer' => fake()->name(),
            'land_area_m2' => fake()->randomFloat(2, 20, 500),
            'land_status' => fake()->randomElement(['Milik Pribadi', 'Fasos/Fasum', 'Pinjam Pakai']),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
