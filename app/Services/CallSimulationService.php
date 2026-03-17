<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Cdr;

class CallSimulationService
{
    public function __construct(
        private BillingService $billing,
    ) {}

    /**
     * Start a new call for the given account.
     */
    public function startCall(Account $account): Cdr
    {
        return Cdr::create([
            'account_id' => $account->id,
            'uniqueid' => uniqid('sim-', true),
            'src' => $account->number,
            'dst' => $this->generateDestination(),
            'started_at' => now(),
            'answered_at' => now()->addSeconds(rand(1, 3)),
            'status' => 'active',
            'disposition' => 'ANSWERED',
        ]);
    }

    /**
     * Update call duration.
     */
    public function updateCall(Cdr $cdr, int $elapsedSeconds): Cdr
    {
        $cdr->update(['duration' => $elapsedSeconds]);

        return $cdr;
    }

    /**
     * End call: calculate cost, charge account, update CDR.
     */
    public function endCall(Cdr $cdr): Cdr
    {
        $account = $cdr->account;
        $tariff = $account->tariff;

        $billsec = max(0, $cdr->duration - $tariff->free_seconds);
        $cost = $this->billing->calculateCost($tariff, $cdr->duration);

        $cdr->update([
            'ended_at' => now(),
            'billsec' => $billsec,
            'cost' => $cost,
            'status' => 'completed',
        ]);

        $this->billing->chargeAccount($account, $cost);

        return $cdr->fresh();
    }

    /**
     * Generate a random destination phone number.
     */
    public function generateDestination(): string
    {
        return '79' . str_pad((string) random_int(0, 999999999), 9, '0', STR_PAD_LEFT);
    }

    /**
     * Determine if a call should end based on duration or random chance.
     */
    public function shouldEndCall(int $currentDuration, int $maxDuration): bool
    {
        return $currentDuration >= $maxDuration || random_int(1, 100) <= 20;
    }
}
