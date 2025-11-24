<?php

namespace Database\Factories;

use App\Models\ValabilitateCursa;
use App\Models\ValabilitateCursaStop;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\ValabilitateCursaStop>
 */
class ValabilitateCursaStopFactory extends Factory
{
    protected $model = ValabilitateCursaStop::class;

    public function definition(): array
    {
        return [
            'valabilitate_cursa_id' => ValabilitateCursa::factory(),
            'type' => fake()->randomElement(['incarcare', 'descarcare']),
            'cod_postal' => fake()->postcode(),
            'localitate' => fake()->city(),
            'position' => fake()->numberBetween(1, 10),
        ];
    }
}
