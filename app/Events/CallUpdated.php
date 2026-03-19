<?php

namespace App\Events;

use App\Models\Cdr;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class CallUpdated implements ShouldBroadcastNow
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
        return 'call.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->cdr->id,
            'uniqueid' => $this->cdr->uniqueid,
            'duration' => $this->cdr->duration,
        ];
    }
}
