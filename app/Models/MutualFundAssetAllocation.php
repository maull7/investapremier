<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MutualFundAssetAllocation extends Model
{
    protected $table = 'mutual_fund_asset_allocations';

    protected $fillable = [
        'reksa_dana_id', 'period_date',
        'equity_percent', 'bond_percent',
        'money_market_percent', 'cash_percent',
    ];

    protected $casts = [
        'period_date' => 'date',
        'equity_percent' => 'decimal:2',
        'bond_percent' => 'decimal:2',
        'money_market_percent' => 'decimal:2',
        'cash_percent' => 'decimal:2',
    ];

    public function reksaDana(): BelongsTo
    {
        return $this->belongsTo(ReksaDana::class, 'reksa_dana_id');
    }
}
