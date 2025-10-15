<?php

namespace Database\Factories\Service;

use App\Models\FacturiFurnizori\FacturaFurnizor;
use App\Models\Service\GestiunePiesa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Service\GestiunePiesa>
 */
class GestiunePiesaFactory extends Factory
{
    protected $model = GestiunePiesa::class;

    public function definition(): array
    {
        return [
            'factura_id' => FacturaFurnizor::factory(),
            'denumire' => $this->faker->words(3, true),
            'cod' => strtoupper($this->faker->bothify('PIE###')),
            'nr_bucati' => $this->faker->randomFloat(2, 1, 20),
            'pret' => $this->faker->randomFloat(2, 10, 500),
        ];
    }
}
