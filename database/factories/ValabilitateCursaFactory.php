<?php

namespace Database\Factories;

use App\Models\Tara;
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
            'nr_ordine' => fake()->numberBetween(1, 20),
            'nr_cursa' => fake()->optional()->bothify('Cursa-###'),
            'incarcare_localitate' => fake()->city(),
            'incarcare_cod_postal' => fake()->postcode(),
            'incarcare_tara_id' => Tara::factory(),
            'descarcare_localitate' => fake()->city(),
            'descarcare_cod_postal' => fake()->postcode(),
            'descarcare_tara_id' => Tara::factory(),
            'data_cursa' => fake()->dateTimeBetween('-1 week', '+1 week'),
            'observatii' => fake()->optional()->sentence(),
            'km_bord_incarcare' => fake()->numberBetween(0, 500000),
            'km_bord_descarcare' => fake()->numberBetween(0, 500000),
            'km_maps' => (string) fake()->numberBetween(0, 5000),
            'km_maps_gol' => fake()->numberBetween(0, 5000),
            'km_maps_plin' => fake()->numberBetween(0, 5000),
            'km_cu_taxa' => fake()->numberBetween(0, 5000),
            'km_flash_gol' => fake()->numberBetween(0, 5000),
            'km_flash_plin' => fake()->numberBetween(0, 5000),
        ];
    }
}
