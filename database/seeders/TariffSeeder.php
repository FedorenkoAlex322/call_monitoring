<?php

namespace Database\Seeders;

use App\Models\Tariff;
use Illuminate\Database\Seeder;

class TariffSeeder extends Seeder
{
    public function run(): void
    {
        $tariffs = [
            [
                'name' => 'Базовый',
                'price_per_minute' => 1.5000,
                'connection_fee' => 0,
                'free_seconds' => 5,
                'description' => 'Базовый тариф для обычных звонков',
            ],
            [
                'name' => 'Бизнес',
                'price_per_minute' => 0.8000,
                'connection_fee' => 1.00,
                'free_seconds' => 10,
                'description' => 'Тариф для бизнес-клиентов',
            ],
            [
                'name' => 'VIP',
                'price_per_minute' => 0.5000,
                'connection_fee' => 0,
                'free_seconds' => 30,
                'description' => 'VIP тариф с максимальными привилегиями',
            ],
        ];

        foreach ($tariffs as $tariff) {
            Tariff::firstOrCreate(['name' => $tariff['name']], $tariff);
        }
    }
}
