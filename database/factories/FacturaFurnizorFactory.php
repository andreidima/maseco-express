<?php

namespace Database\Factories;

use App\Models\FacturiFurnizori\FacturaFurnizor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<FacturaFurnizor>
 */
class FacturaFurnizorFactory extends Factory
{
    protected $model = FacturaFurnizor::class;

    public function definition(): array
    {
        $dataFactura = $this->faker->dateTimeBetween('-3 months', 'now');
        $dataScadenta = Carbon::instance($dataFactura)->copy()->addDays($this->faker->numberBetween(5, 45));

        return [
            'denumire_furnizor' => $this->faker->company(),
            'numar_factura' => strtoupper($this->faker->bothify('FF-#####')),
            'data_factura' => $dataFactura,
            'data_scadenta' => $dataScadenta,
            'suma' => $this->faker->randomFloat(2, 100, 5000),
            'moneda' => $this->faker->randomElement(['RON', 'EUR', 'USD']),
            'departament_vehicul' => $this->faker->optional()->word(),
            'observatii' => $this->faker->optional()->sentence(),
            'status' => FacturaFurnizor::STATUS_NEPLATITA,
        ];
    }

    public function platita(): self
    {
        return $this->state(fn () => ['status' => FacturaFurnizor::STATUS_PLATITA]);
    }

    public function inCalup(): self
    {
        return $this->state(fn () => ['status' => FacturaFurnizor::STATUS_PLATITA]);
    }
}
