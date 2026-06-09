<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockPriceAlert extends Model
{
    protected $fillable = [
        'user_id',
        'stock_id',
        'kode_efek',
        'condition',
        'target_price',
        'note',
        'is_active',
        'repeat',
        'last_seen_price',
        'triggered_at',
    ];

    protected $casts = [
        'target_price' => 'decimal:2',
        'last_seen_price' => 'decimal:2',
        'is_active' => 'boolean',
        'repeat' => 'boolean',
        'triggered_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }

    public function isConditionMet(float $price): bool
    {
        return $this->condition === 'above'
            ? $price >= (float) $this->target_price
            : $price <= (float) $this->target_price;
    }

    public function conditionLabel(): string
    {
        return $this->condition === 'above' ? 'menyentuh / di atas' : 'menyentuh / di bawah';
    }
}
