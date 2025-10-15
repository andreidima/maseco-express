<?php

namespace Database\Factories\Service;

use App\Models\Service\Masina;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Service\Masina>
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
