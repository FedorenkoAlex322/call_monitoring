<?php

namespace Tests\Feature\Events;

use App\Events\BalanceUpdated;
use App\Events\CallEnded;
use App\Events\CallStarted;
use App\Events\CallUpdated;
use App\Models\Account;
use App\Models\Cdr;
use App\Models\Tariff;
use App\Models\User;
use App\Services\BillingService;
use App\Services\CallSimulationService;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class CallEventsTest extends TestCase
{
    use RefreshDatabase;

    private function createActiveAccount(): Account
    {
        $user = User::factory()->create();
        $tariff = Tariff::factory()->create([
            'price_per_minute' => 1.0,
            'connection_fee' => 0,
            'free_seconds' => 0,
        ]);
        return Account::factory()->create([
            'user_id' => $user->id,
            'tariff_id' => $tariff->id,
            'balance' => 500.00,
            'status' => 'active',
        ]);
    }

    public function test_call_started_event_dispatched(): void
    {
        Event::fake([CallStarted::class]);
        $account = $this->createActiveAccount();

        $service = app(CallSimulationService::class);
        $service->startCall($account);

        Event::assertDispatched(CallStarted::class);
    }

    public function test_call_updated_event_dispatched(): void
    {
        Event::fake([CallUpdated::class]);
        $account = $this->createActiveAccount();

        $service = app(CallSimulationService::class);
        $cdr = Cdr::factory()->create([
            'account_id' => $account->id,
            'status' => 'active',
        ]);
        $service->updateCall($cdr, 10);

        Event::assertDispatched(CallUpdated::class);
    }

    public function test_call_ended_and_balance_updated_dispatched(): void
    {
        Event::fake([CallEnded::class, BalanceUpdated::class]);
        $account = $this->createActiveAccount();

        $cdr = Cdr::factory()->create([
            'account_id' => $account->id,
            'status' => 'active',
            'duration' => 60,
        ]);

        $service = app(CallSimulationService::class);
        $service->endCall($cdr);

        Event::assertDispatched(CallEnded::class);
        Event::assertDispatched(BalanceUpdated::class);
    }

    public function test_call_started_broadcasts_on_correct_channels(): void
    {
        $account = $this->createActiveAccount();
        $cdr = Cdr::factory()->create(['account_id' => $account->id]);

        $event = new CallStarted($cdr);
        $channels = $event->broadcastOn();

        $this->assertCount(1, $channels);
        $this->assertInstanceOf(Channel::class, $channels[0]);
    }

    public function test_balance_updated_broadcasts_on_private_channel(): void
    {
        $account = $this->createActiveAccount();
        $event = new BalanceUpdated($account);
        $channels = $event->broadcastOn();

        $this->assertCount(1, $channels);
        $this->assertInstanceOf(PrivateChannel::class, $channels[0]);
    }

    public function test_call_ended_payload_contains_required_fields(): void
    {
        $account = $this->createActiveAccount();
        $cdr = Cdr::factory()->create([
            'account_id' => $account->id,
            'status' => 'completed',
            'ended_at' => now(),
        ]);

        $event = new CallEnded($cdr);
        $payload = $event->broadcastWith();

        $this->assertArrayHasKey('id', $payload);
        $this->assertArrayHasKey('uniqueid', $payload);
        $this->assertArrayHasKey('duration', $payload);
        $this->assertArrayHasKey('cost', $payload);
        $this->assertArrayHasKey('disposition', $payload);
        $this->assertArrayHasKey('ended_at', $payload);
    }
}
