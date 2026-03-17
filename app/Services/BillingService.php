<?php

namespace App\Services;

use App\Models\Tariff;

class BillingService
{
    /**
     * Calculate call cost based on tariff and duration using bcmath for precision.
     */
    public function calculateCost(Tariff $tariff, int $durationSeconds): string
    {
        $billableSeconds = max(0, $durationSeconds - $tariff->free_seconds);

        if ($billableSeconds === 0) {
            return $tariff->connection_fee;
        }

        $minutes = (string) ceil($billableSeconds / 60);
        $cost = bcadd(
            bcmul($minutes, $tariff->price_per_minute, 4),
            $tariff->connection_fee,
            2
        );

        return $cost;
    }

    /**
     * Calculate billable seconds based on tariff free seconds.
     * Single source of truth for billsec calculation.
     */
    public function calculateBillsec(Tariff $tariff, int $durationSeconds): int
    {
        return max(0, $durationSeconds - $tariff->free_seconds);
    }
}

