<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Tariff;
use Illuminate\Support\Facades\DB;

class BillingService
{
    /**
     * Calculate call cost based on tariff and duration.
     */
    public function calculateCost(Tariff $tariff, int $durationSeconds): float
    {
        $billableSeconds = max(0, $durationSeconds - $tariff->free_seconds);

        if ($billableSeconds === 0) {
            return (float) $tariff->connection_fee;
        }

        $minutes = ceil($billableSeconds / 60);
        $cost = ($minutes * $tariff->price_per_minute) + $tariff->connection_fee;

        return round($cost, 2);
    }

    /**
     * Charge account balance within a transaction with row locking.
     */
    public function chargeAccount(Account $account, float $amount): bool
    {
        DB::transaction(function () use ($account, $amount) {
            $account = Account::lockForUpdate()->find($account->id);
            $account->balance -= $amount;
            $account->save();
        });

        return true;
    }
}
