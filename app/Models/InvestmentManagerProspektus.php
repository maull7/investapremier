<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestmentManagerProspektus extends Model
{
    protected $table = 'investment_manager_prospektus';
    protected $fillable = [
        'investment_manager_id',
        'reksa_dana_id',
        'tahun',
        'data',
    ];

    protected $casts = [
        'data'  => 'array',
        'tahun' => 'integer',
    ];

    public function manager(): BelongsTo
    {
        return $this->belongsTo(InvestmentManager::class, 'investment_manager_id');
    }

    public function reksaDana(): BelongsTo
    {
        return $this->belongsTo(ReksaDana::class, 'reksa_dana_id');
    }
}
