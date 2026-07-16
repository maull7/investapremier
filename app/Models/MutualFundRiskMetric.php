<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MutualFundRiskMetric extends Model
{
    protected $table = 'mutual_fund_risk_metrics';

protected $fillable = [
        'reksa_dana_id',
        'period_date',
        'sharpe_ratio_1m',
        'sharpe_ratio_3m',
        'sharpe_ratio_6m',
        'sharpe_ratio_1y',
        'sharpe_ratio_3y',
        'sharpe_ratio_5y',
        'sharpe_ratio_10y',
        'stdev_1m',
        'stdev_3m',
        'stdev_6m',
        'stdev_1y',
        'stdev_3y',
        'stdev_5y',
        'stdev_10y',
        'beta_1m',
        'beta_3m',
        'beta_6m',
        'beta_1y',
        'beta_3y',
        'beta_5y',
        'beta_10y',
        'max_drawdown_1m',
        'max_drawdown_3m',
        'max_drawdown_6m',
        'max_drawdown_1y',
        'max_drawdown_3y',
        'max_drawdown_5y',
        'max_drawdown_10y',
    ];

    protected $casts = [
        'period_date' => 'date',
        'sharpe_ratio_1m' => 'decimal:6',
        'sharpe_ratio_3m' => 'decimal:6',
        'sharpe_ratio_6m' => 'decimal:6',
        'sharpe_ratio_1y' => 'decimal:6',
        'sharpe_ratio_3y' => 'decimal:6',
        'sharpe_ratio_5y' => 'decimal:6',
        'sharpe_ratio_10y' => 'decimal:6',
        'stdev_1m' => 'decimal:6',
        'stdev_3m' => 'decimal:6',
        'stdev_6m' => 'decimal:6',
        'stdev_1y' => 'decimal:6',
        'stdev_3y' => 'decimal:6',
        'stdev_5y' => 'decimal:6',
        'stdev_10y' => 'decimal:6',
        'beta_1m' => 'decimal:6',
        'beta_3m' => 'decimal:6',
        'beta_6m' => 'decimal:6',
        'beta_1y' => 'decimal:6',
        'beta_3y' => 'decimal:6',
        'beta_5y' => 'decimal:6',
        'beta_10y' => 'decimal:6',
        'max_drawdown_1m' => 'decimal:6',
        'max_drawdown_3m' => 'decimal:6',
        'max_drawdown_6m' => 'decimal:6',
        'max_drawdown_1y' => 'decimal:6',
        'max_drawdown_3y' => 'decimal:6',
        'max_drawdown_5y' => 'decimal:6',
        'max_drawdown_10y' => 'decimal:6',
    ];

    public function reksaDana(): BelongsTo
    {
        return $this->belongsTo(ReksaDana::class, 'reksa_dana_id');
    }
}