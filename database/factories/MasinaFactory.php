<?php

namespace Database\Factories;

use App\Models\Masina;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Masina>
 */
class MasinaFactory extends Factory
{
    protected $model = Masina::class;

    public function definition(): array
    {
        return [
            'denumire' => $this->faker->company . ' ' . $this->faker->word(),
            'numar_inmatriculare' => strtoupper($this->faker->bothify('??##???')),
            'serie_sasiu' => strtoupper($this->faker->bothify('###########')),
            'observatii' => $this->faker->sentence(),
        ];
    }
}
