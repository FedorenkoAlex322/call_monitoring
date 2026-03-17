<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tariff extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'price_per_minute', 'connection_fee', 'free_seconds', 'description', 'is_active'];

    protected function casts(): array
    {
        return [
            'price_per_minute' => 'decimal:4',
            'connection_fee' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }
}
