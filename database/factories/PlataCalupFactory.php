<?php

namespace Database\Factories;

use App\Models\FacturiFurnizori\PlataCalup;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<PlataCalup>
 */
class PlataCalupFactory extends Factory
{
    protected $model = PlataCalup::class;

    public function definition(): array
    {
        return [
            'denumire_calup' => 'Calup ' . strtoupper($this->faker->bothify('PLT-##')),
            'data_plata' => null,
            'fisier_pdf' => null,
            'observatii' => $this->faker->optional()->sentence(),
            'status' => PlataCalup::STATUS_DESCHIS,
        ];
    }

    public function platit(): self
    {
        return $this->state(function () {
            return [
                'status' => PlataCalup::STATUS_PLATIT,
                'data_plata' => Carbon::now()->subDays($this->faker->numberBetween(0, 15)),
            ];
        });
    }
}
