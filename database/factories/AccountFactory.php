<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Tariff;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccountFactory extends Factory
{
    protected $model = Account::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'tariff_id' => Tariff::factory(),
            'number' => (string) fake()->unique()->numberBetween(1001, 9999),
            'name' => fake()->name(),
            'balance' => fake()->randomFloat(2, 10, 1000),
            'status' => 'active',
        ];
    }
}
