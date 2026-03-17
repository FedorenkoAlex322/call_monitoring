<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActiveCallResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uniqueid' => $this->uniqueid,
            'src' => $this->src,
            'dst' => $this->dst,
            'started_at' => $this->started_at->toISOString(),
            'duration' => $this->duration,
            'status' => $this->status,
        ];
    }
}
