<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'name' => $this->name,
            'balance' => (float) $this->balance,
            'status' => $this->status,
            'tariff' => [
                'id' => $this->tariff->id,
                'name' => $this->tariff->name,
                'price_per_minute' => (float) $this->tariff->price_per_minute,
                'connection_fee' => (float) $this->tariff->connection_fee,
                'free_seconds' => $this->tariff->free_seconds,
            ],
        ];
    }
}
