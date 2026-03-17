<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Tariff;
use App\Models\User;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'admin@asterisk.local')->first();
        $tariffs = Tariff::all();

        // Create main account for admin (1:1 relationship)
        Account::firstOrCreate(
            ['number' => '1001'],
            [
                'user_id' => $user->id,
                'tariff_id' => $tariffs->random()->id,
                'name' => 'Admin Line',
                'balance' => 500.00,
                'status' => 'active',
            ]
        );

        // Create additional demo accounts with their own users
        for ($i = 2; $i <= 5; $i++) {
            $demoUser = User::firstOrCreate(
                ['email' => "user{$i}@asterisk.local"],
                [
                    'name' => "User {$i}",
                    'password' => bcrypt('password'),
                ]
            );

            Account::firstOrCreate(
                ['number' => "100{$i}"],
                [
                    'user_id' => $demoUser->id,
                    'tariff_id' => $tariffs->random()->id,
                    'name' => "Line 100{$i}",
                    'balance' => fake()->randomFloat(2, 100, 1000),
                    'status' => 'active',
                ]
            );
        }
    }
}
