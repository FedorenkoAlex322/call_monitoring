<?php

namespace App\Events;

use App\Models\Cdr;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallEnded implements ShouldBroadcast
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
        return 'call.ended';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->cdr->id,
            'uniqueid' => $this->cdr->uniqueid,
            'account_id' => $this->cdr->account_id,
            'src' => $this->cdr->src,
            'dst' => $this->cdr->dst,
            'duration' => $this->cdr->duration,
            'billsec' => $this->cdr->billsec,
            'cost' => (float) $this->cdr->cost,
            'disposition' => $this->cdr->disposition,
            'ended_at' => $this->cdr->ended_at->toISOString(),
        ];
    }
}
