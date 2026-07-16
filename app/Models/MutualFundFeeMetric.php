<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MutualFundFeeMetric extends Model
{
    protected $table = 'mutual_fund_fee_metrics';

    protected $fillable = [
        'reksa_dana_id',
        'period_date',
        'management_fee',
        'custodian_fee',
        'expense_ratio',
        'subscription_fee',
        'redemption_fee',
        'switching_fee',
        'investment_manager_fee',
    ];

    protected $casts = [
        'period_date' => 'date',
        'management_fee' => 'decimal:4',
        'custodian_fee' => 'decimal:4',
        'expense_ratio' => 'decimal:6',
        'subscription_fee' => 'decimal:4',
        'redemption_fee' => 'decimal:4',
        'switching_fee' => 'decimal:4',
        'investment_manager_fee' => 'decimal:4',
    ];

    public function reksaDana(): BelongsTo
    {
        return $this->belongsTo(ReksaDana::class, 'reksa_dana_id');
    }
}