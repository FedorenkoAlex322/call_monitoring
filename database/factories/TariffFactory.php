<?php

namespace Database\Factories;

use App\Models\Tariff;
use Illuminate\Database\Eloquent\Factories\Factory;

class TariffFactory extends Factory
{
    protected $model = Tariff::class;

    public function definition(): array
    {
        return [
            'name' => fake()->word() . ' Plan',
            'price_per_minute' => fake()->randomFloat(4, 0.5, 5.0),
            'connection_fee' => fake()->randomFloat(2, 0, 2.0),
            'free_seconds' => fake()->randomElement([0, 5, 10, 30]),
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}
