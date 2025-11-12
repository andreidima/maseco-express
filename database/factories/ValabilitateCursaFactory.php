<?php

namespace Database\Factories;

use App\Models\Valabilitate;
use App\Models\ValabilitateCursa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\ValabilitateCursa>
 */
class ValabilitateCursaFactory extends Factory
{
    protected $model = ValabilitateCursa::class;

    public function definition(): array
    {
        return [
            'valabilitate_id' => Valabilitate::factory(),
            'incarcare_localitate' => fake()->city(),
            'incarcare_cod_postal' => fake()->postcode(),
            'descarcare_localitate' => fake()->city(),
            'descarcare_cod_postal' => fake()->postcode(),
            'data_cursa' => fake()->dateTimeBetween('-1 week', '+1 week'),
            'observatii' => fake()->optional()->sentence(),
        ];
    }
}
