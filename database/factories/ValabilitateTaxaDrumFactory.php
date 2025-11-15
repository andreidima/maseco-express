<?php

namespace Database\Factories;

use App\Models\Valabilitate;
use App\Models\ValabilitateTaxaDrum;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\ValabilitateTaxaDrum>
 */
class ValabilitateTaxaDrumFactory extends Factory
{
    protected $model = ValabilitateTaxaDrum::class;

    public function definition(): array
    {
        return [
            'valabilitate_id' => Valabilitate::factory(),
            'nume' => fake()->words(3, true),
            'tara' => fake()->country(),
            'suma' => fake()->randomFloat(2, 1, 500),
            'moneda' => strtoupper(fake()->currencyCode()),
            'data' => fake()->date(),
            'observatii' => fake()->optional()->sentence(),
        ];
    }
}
