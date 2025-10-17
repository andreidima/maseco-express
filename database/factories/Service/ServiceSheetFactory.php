<?php

namespace Database\Factories\Service;

use App\Models\Service\Masina;
use App\Models\Service\ServiceSheet;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceSheetFactory extends Factory
{
    protected $model = ServiceSheet::class;

    public function definition(): array
    {
        return [
            'masina_id' => Masina::factory(),
            'km_bord' => $this->faker->numberBetween(0, 400000),
            'data_service' => $this->faker->date(),
        ];
    }
}
