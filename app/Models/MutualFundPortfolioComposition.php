<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MutualFundPortfolioComposition extends Model
{
    protected $table = 'mutual_fund_portfolio_compositions';

    protected $fillable = [
        'reksa_dana_id', 'period_date',
        'security_name', 'security_type', 'weight_percent',
    ];

    protected $casts = [
        'period_date' => 'date',
        'weight_percent' => 'decimal:2',
    ];

    public function reksaDana(): BelongsTo
    {
        return $this->belongsTo(ReksaDana::class, 'reksa_dana_id');
    }
}
