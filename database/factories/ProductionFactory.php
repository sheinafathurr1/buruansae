<?php

namespace Database\Factories;

use App\Models\Commodity;
use App\Models\FarmerGroup;
use App\Models\Production;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * Default: siklus yang sudah dipanen.
 *
 * @extends Factory<Production>
 */
class ProductionFactory extends Factory
{
    public function definition(): array
    {
        $start = Carbon::today()->subDays(fake()->numberBetween(40, 160));
        $harvest = $start->copy()->addDays(fake()->numberBetween(25, 35));

        return [
            'farmer_group_id' => FarmerGroup::factory(),
            'commodity_id' => Commodity::factory(),
            'start_date' => $start,
            'initial_quantity' => fake()->numberBetween(10, 200),
            'estimated_harvest_date' => $harvest,
            'estimated_harvest_quantity' => fake()->randomFloat(3, 2, 50),
            'harvest_date' => $harvest,
            'harvest_quantity' => fake()->randomFloat(3, 2, 50),
            'selling_price' => fake()->numberBetween(5, 30) * 1000,
        ];
    }

    public function harvestedOn(string $date, float $quantity): static
    {
        return $this->state(fn () => ['harvest_date' => $date, 'harvest_quantity' => $quantity]);
    }

    /** Belum dipanen dengan perkiraan panen pada tanggal tertentu. */
    public function pending(string $estimatedDate, float $estimatedQuantity = 10, float $initialQuantity = 50): static
    {
        return $this->state(fn () => [
            'start_date' => Carbon::parse($estimatedDate)->subDays(30),
            'initial_quantity' => $initialQuantity,
            'estimated_harvest_date' => $estimatedDate,
            'estimated_harvest_quantity' => $estimatedQuantity,
            'harvest_date' => null,
            'harvest_quantity' => null,
            'selling_price' => null,
        ]);
    }
}
