<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Valabilitate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Valabilitate>
 */
class ValabilitateFactory extends Factory
{
    protected $model = Valabilitate::class;

    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('-1 month', '+1 week');

        return [
            'numar_auto' => strtoupper(fake()->bothify('??##???')),
            'sofer_id' => User::factory(),
            'denumire' => fake()->words(3, true),
            'data_inceput' => $startDate->format('Y-m-d'),
            'data_sfarsit' => fake()->boolean(40)
                ? fake()->dateTimeBetween($startDate, '+2 months')->format('Y-m-d')
                : null,
        ];
    }
}
