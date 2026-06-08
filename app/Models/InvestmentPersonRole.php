<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestmentPersonRole extends Model
{
    protected $fillable = [
        'person_name',
        'normalized_name',
        'role_type',
        'role_title',
        'investment_manager_id',
        'reksa_dana_id',
        'source',
    ];

    public function investmentManager(): BelongsTo
    {
        return $this->belongsTo(InvestmentManager::class, 'investment_manager_id');
    }

    public function reksaDana(): BelongsTo
    {
        return $this->belongsTo(ReksaDana::class, 'reksa_dana_id');
    }
}
