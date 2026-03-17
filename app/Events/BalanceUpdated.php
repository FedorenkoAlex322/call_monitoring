<?php

namespace App\Events;

use App\Models\Account;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BalanceUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Account $account,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('account.' . $this->account->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'balance.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'account_id' => $this->account->id,
            'balance' => (float) $this->account->balance,
        ];
    }
}
