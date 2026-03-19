<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Services\CallSimulationService;
use DomainException;
use Illuminate\Console\Command;

class SimulateCallsCommand extends Command
{
    protected $signature = 'call:simulate
        {--calls=3 : Number of calls to simulate}
        {--duration=10 : Max call duration in seconds}
        {--interval=2 : Tick interval in seconds}';

    protected $description = 'Simulate realistic call events for testing';

    public function handle(CallSimulationService $service): int
    {
        $callCount = (int) $this->option('calls');
        $maxDuration = (int) $this->option('duration');
        $interval = (int) $this->option('interval');

        $accounts = Account::where('status', 'active')
            ->inRandomOrder()
            ->limit($callCount)
            ->get();

        if ($accounts->isEmpty()) {
            $this->error('No active accounts found. Run db:seed first.');
            return self::FAILURE;
        }

        $this->info("Starting simulation: {$accounts->count()} calls, max {$maxDuration}s, tick every {$interval}s");
        $this->newLine();

        // Phase 1: Start all calls simultaneously
        $activeCalls = [];
        foreach ($accounts as $account) {
            $account->load('tariff');

            try {
                $cdr = $service->startCall($account);
            } catch (DomainException $e) {
                $this->warn("[SKIPPED] {$account->number}: {$e->getMessage()}");
                continue;
            }

            $minDuration = max($interval, (int) ceil($maxDuration / 3));
            $targetDuration = rand($minDuration, $maxDuration);

            $activeCalls[] = [
                'cdr' => $cdr,
                'account' => $account,
                'elapsed' => 0,
                'target' => $targetDuration,
            ];

            $this->info("[STARTED] {$cdr->src} -> {$cdr->dst} (target: {$targetDuration}s)");
        }

        if (empty($activeCalls)) {
            $this->error('No calls could be started.');
            return self::FAILURE;
        }

        $this->newLine();

        // Phase 2: Tick loop — update all active calls, end those that reached target
        while (!empty($activeCalls)) {
            sleep($interval);

            $remaining = [];

            foreach ($activeCalls as $call) {
                $call['elapsed'] += $interval;
                $service->updateCall($call['cdr'], $call['elapsed']);
                $this->line("  [UPDATE] {$call['cdr']->src} duration: {$call['elapsed']}s / {$call['target']}s");

                if ($call['elapsed'] >= $call['target']) {
                    $cdr = $service->endCall($call['cdr']);
                    $this->info("[ENDED]   {$cdr->src} -> {$cdr->dst} | duration: {$cdr->duration}s | cost: {$cdr->cost} | balance: {$call['account']->fresh()->balance}");
                    $this->newLine();
                } else {
                    $remaining[] = $call;
                }
            }

            $activeCalls = $remaining;
        }

        $this->info('Simulation completed.');

        return self::SUCCESS;
    }
}
