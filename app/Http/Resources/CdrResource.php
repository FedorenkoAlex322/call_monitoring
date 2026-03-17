<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CdrResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uniqueid' => $this->uniqueid,
            'src' => $this->src,
            'dst' => $this->dst,
            'started_at' => $this->started_at->toISOString(),
            'answered_at' => $this->answered_at?->toISOString(),
            'ended_at' => $this->ended_at?->toISOString(),
            'duration' => $this->duration,
            'billsec' => $this->billsec,
            'cost' => (float) $this->cost,
            'disposition' => $this->disposition,
        ];
    }
}
