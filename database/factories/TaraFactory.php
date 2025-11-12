<?php

namespace Database\Factories;

use App\Models\Tara;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Tara>
 */
class TaraFactory extends Factory
{
    protected $model = Tara::class;

    public function definition(): array
    {
        return [
            'nume' => fake()->unique()->country(),
            'gmt_offset' => fake()->numberBetween(-12, 14),
        ];
    }
}
