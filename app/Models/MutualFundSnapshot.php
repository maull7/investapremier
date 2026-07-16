<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MutualFundSnapshot extends Model
{
    protected $table = 'mutual_fund_snapshots';

    protected $fillable = [
        'reksa_dana_id',
        'period_date',
        'nab_per_unit',
        'aum',
        'total_unit',
        'return_1m',
        'return_3m',
        'return_6m',
        'return_ytd',
        'return_1y',
        'return_3y',
        'return_5y',
        'return_10y',
        'return_inception',
    ];

    protected $casts = [
        'period_date' => 'date',
        'nab_per_unit' => 'decimal:6',
        'aum' => 'decimal:2',
        'total_unit' => 'decimal:2',
        'return_1m' => 'decimal:6',
        'return_3m' => 'decimal:6',
        'return_6m' => 'decimal:6',
        'return_ytd' => 'decimal:6',
        'return_1y' => 'decimal:6',
        'return_3y' => 'decimal:6',
        'return_5y' => 'decimal:6',
        'return_10y' => 'decimal:6',
        'return_inception' => 'decimal:6',
    ];

    public function reksaDana(): BelongsTo
    {
        return $this->belongsTo(ReksaDana::class, 'reksa_dana_id');
    }
}