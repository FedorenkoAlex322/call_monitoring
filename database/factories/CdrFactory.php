<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Cdr;
use Illuminate\Database\Eloquent\Factories\Factory;

class CdrFactory extends Factory
{
    protected $model = Cdr::class;

    public function definition(): array
    {
        $startedAt = fake()->dateTimeBetween('-1 hour', 'now');
        $duration = fake()->numberBetween(5, 300);

        return [
            'account_id' => Account::factory(),
            'uniqueid' => fake()->unique()->uuid(),
            'src' => (string) fake()->numberBetween(1001, 9999),
            'dst' => '79' . fake()->numerify('#########'),
            'started_at' => $startedAt,
            'answered_at' => $startedAt,
            'ended_at' => (clone $startedAt)->modify("+{$duration} seconds"),
            'duration' => $duration,
            'billsec' => $duration,
            'cost' => fake()->randomFloat(2, 0.5, 50),
            'disposition' => 'ANSWERED',
            'status' => 'completed',
        ];
    }
}
