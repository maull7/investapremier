<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvestmentManagerPeriod extends Model
{
    protected $fillable = [
        'investment_manager_id', 'period_date', 'aum', 'up',
        'mata_uang', 'tahun', 'kuartal',
    ];

    protected $casts = [
        'period_date' => 'date',
        'aum' => 'decimal:2',
        'up' => 'decimal:2',
    ];

    public function manager()
    {
        return $this->belongsTo(InvestmentManager::class, 'investment_manager_id');
    }
}
