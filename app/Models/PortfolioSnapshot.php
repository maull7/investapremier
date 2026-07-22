<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortfolioSnapshot extends Model
{
    protected $fillable = [
        'user_id',
        'total_value',
        'asset_value',
        'cash_value',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'total_value' => 'decimal:2',
            'asset_value' => 'decimal:2',
            'cash_value' => 'decimal:2',
            'recorded_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
