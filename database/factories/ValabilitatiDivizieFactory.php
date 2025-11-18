<?php

namespace Database\Factories;

use App\Models\ValabilitatiDivizie;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\ValabilitatiDivizie>
 */
class ValabilitatiDivizieFactory extends Factory
{
    protected $model = ValabilitatiDivizie::class;

    public function definition(): array
    {
        return [
            'nume' => fake()->unique()->company(),
        ];
    }
}
