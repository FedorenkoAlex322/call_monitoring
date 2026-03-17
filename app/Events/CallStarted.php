<?php

namespace App\Events;

use App\Models\Cdr;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallStarted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Cdr $cdr,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('calls'),
            new PrivateChannel('account.' . $this->cdr->account_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'call.started';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->cdr->id,
            'uniqueid' => $this->cdr->uniqueid,
            'account_id' => $this->cdr->account_id,
            'src' => $this->cdr->src,
            'dst' => $this->cdr->dst,
            'started_at' => $this->cdr->started_at->toISOString(),
            'status' => 'active',
        ];
    }
}
