<?php

namespace Database\Factories;

use App\Models\Distribution;
use App\Models\Production;
use App\Models\RecipientCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Distribution>
 */
class DistributionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'production_id' => Production::factory(),
            'recipient_category_id' => fn () => RecipientCategory::query()->inRandomOrder()->value('id'),
            'quantity' => fake()->randomFloat(3, 0.5, 10),
            'household_count' => fake()->numberBetween(1, 10),
            'person_count' => fake()->numberBetween(1, 30),
        ];
    }
}
