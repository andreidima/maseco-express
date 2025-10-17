<?php

namespace Database\Factories\Service;

use App\Models\Service\ServiceSheet;
use App\Models\Service\ServiceSheetItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceSheetItemFactory extends Factory
{
    protected $model = ServiceSheetItem::class;

    public function definition(): array
    {
        return [
            'service_sheet_id' => ServiceSheet::factory(),
            'position' => $this->faker->numberBetween(1, 20),
            'description' => $this->faker->sentence(3),
            'quantity' => (string) $this->faker->numberBetween(1, 5),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
