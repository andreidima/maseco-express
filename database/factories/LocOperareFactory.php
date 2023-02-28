<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LocOperare>
 */
class LocOperareFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nume' => fake()->company(),
            'tara_id' => fake()->numberBetween(1, 193),
            'judet' => fake()->city(),
            'oras' => fake()->city(),
            'adresa' => fake()->address(),
            'cod_postal' => fake()->numberBetween(100000, 999999),
            'persoana_contact' => fake()->name(),
            'telefon' => '07' . fake()->numberBetween(10000000, 99999999),
            'observatii' => fake()->text(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
