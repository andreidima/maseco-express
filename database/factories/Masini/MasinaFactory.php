<?php

namespace Database\Factories\Masini;

use App\Models\Masini\Masina;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Masini\Masina>
 */
class MasinaFactory extends Factory
{
    protected $model = Masina::class;

    public function definition(): array
    {
        return [
            'numar_inmatriculare' => strtoupper($this->faker->bothify('??##???')),
            'descriere' => $this->faker->optional()->sentence(3),
        ];
    }
}
