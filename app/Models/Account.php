<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'tariff_id', 'number', 'name', 'balance', 'status'];

    protected function casts(): array
    {
        return [
            'balance' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tariff(): BelongsTo
    {
        return $this->belongsTo(Tariff::class);
    }

    public function cdrs(): HasMany
    {
        return $this->hasMany(Cdr::class);
    }

    public function activeCalls(): HasMany
    {
        return $this->hasMany(Cdr::class)->where('status', 'active');
    }
}
