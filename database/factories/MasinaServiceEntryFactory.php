<?php

namespace Database\Factories;

use App\Models\Masina;
use App\Models\MasinaServiceEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\MasinaServiceEntry>
 */
class MasinaServiceEntryFactory extends Factory
{
    protected $model = MasinaServiceEntry::class;

    public function definition(): array
    {
        $tip = $this->faker->randomElement(['piesa', 'manual']);

        return [
            'masina_id' => Masina::factory(),
            'tip' => $tip,
            'data_montaj' => $this->faker->date(),
            'nume_mecanic' => $this->faker->name(),
            'nume_utilizator' => $this->faker->name(),
            'observatii' => $this->faker->sentence(),
            'denumire_piesa' => $tip === 'piesa' ? $this->faker->words(3, true) : null,
            'cod_piesa' => $tip === 'piesa' ? strtoupper($this->faker->bothify('PIE###')) : null,
            'cantitate' => $tip === 'piesa' ? $this->faker->randomFloat(2, 1, 5) : null,
            'denumire_interventie' => $tip === 'manual' ? $this->faker->sentence(3) : null,
        ];
    }
}
