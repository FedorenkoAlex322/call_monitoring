<?php

namespace App\Events;

use App\Models\Cdr;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class CallStarted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public readonly Cdr $cdr,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('calls'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'call.started';
    }

    public function broadcastWith(): array
    {
        $this->cdr->load('account.user');

        return [
            'id' => $this->cdr->id,
            'uniqueid' => $this->cdr->uniqueid,
            'account_id' => $this->cdr->account_id,
            'dst' => $this->cdr->dst,
            'duration' => 0,
            'started_at' => $this->cdr->started_at->toISOString(),
            'account_number' => $this->cdr->account->number,
            'user_name' => $this->cdr->account->user->name,
        ];
    }
}
