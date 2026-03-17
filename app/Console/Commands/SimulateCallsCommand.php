<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Services\CallSimulationService;
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

        foreach ($accounts as $account) {
            $account->load('tariff');

            // Start call
            $cdr = $service->startCall($account);
            $this->info("[STARTED] {$cdr->src} -> {$cdr->dst} (uniqueid: {$cdr->uniqueid})");

            // Duration ticks
            $elapsed = 0;
            while (true) {
                sleep($interval);
                $elapsed += $interval;

                $service->updateCall($cdr, $elapsed);
                $this->line("  [UPDATE] {$cdr->src} duration: {$elapsed}s");

                if ($service->shouldEndCall($elapsed, $maxDuration)) {
                    break;
                }
            }

            // End call
            $cdr = $service->endCall($cdr);
            $this->info("[ENDED]   {$cdr->src} -> {$cdr->dst} | duration: {$cdr->duration}s | cost: {$cdr->cost} | balance: {$account->fresh()->balance}");
            $this->newLine();

            // Random pause between calls
            if ($account !== $accounts->last()) {
                $pause = rand(1, 3);
                $this->line("  Waiting {$pause}s before next call...");
                sleep($pause);
            }
        }

        $this->info('Simulation completed.');

        return self::SUCCESS;
    }
}
