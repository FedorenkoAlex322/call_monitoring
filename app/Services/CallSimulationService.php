<?php

namespace App\Services;

use App\Events\BalanceUpdated;
use App\Events\CallEnded;
use App\Events\CallStarted;
use App\Events\CallUpdated;
use App\Models\Account;
use App\Models\Cdr;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CallSimulationService
{
    public function __construct(
        private BillingService $billing,
    ) {}

    /**
     * Start a new call for the given account.
     * Validates account status and balance before creating CDR.
     */
    public function startCall(Account $account): Cdr
    {
        if ($account->status !== 'active') {
            throw new DomainException("Account {$account->id} is not active");
        }

        if ($account->balance <= 0) {
            throw new DomainException("Account {$account->id} has insufficient balance");
        }

        $cdr = Cdr::create([
            'account_id' => $account->id,
            'uniqueid' => 'sim-' . Str::ulid(),
            'src' => $account->number,
            'dst' => $this->generateDestination(),
            'started_at' => now(),
            'answered_at' => now()->addSeconds(rand(1, 3)),
            'status' => 'active',
            'disposition' => 'ANSWERED',
        ]);

        event(new CallStarted($cdr));

        return $cdr;
    }

    /**
     * Update call duration.
     */
    public function updateCall(Cdr $cdr, int $elapsedSeconds): Cdr
    {
        $cdr->update(['duration' => $elapsedSeconds]);

        event(new CallUpdated($cdr));

        return $cdr;
    }

    /**
     * End call atomically: lock account, calculate cost, update CDR, charge balance.
     * All operations within a single transaction to prevent partial writes.
     */
    public function endCall(Cdr $cdr): Cdr
    {
        return DB::transaction(function () use ($cdr) {
            $account = Account::lockForUpdate()->find($cdr->account_id);
            $tariff = $account->tariff;

            $billsec = $this->billing->calculateBillsec($tariff, $cdr->duration);
            $cost = $this->billing->calculateCost($tariff, $cdr->duration);

            $cdr->update([
                'ended_at' => now(),
                'billsec' => $billsec,
                'cost' => $cost,
                'status' => 'completed',
            ]);

            $account->balance -= $cost;
            $account->save();

            event(new CallEnded($cdr));
            event(new BalanceUpdated($account));

            return $cdr;
        });
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
