<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MutualFundManagementTeam extends Model
{
    protected $table = 'mutual_fund_management_teams';

    protected $fillable = [
        'reksa_dana_id', 'type', 'name', 'position',
    ];

    public function reksaDana(): BelongsTo
    {
        return $this->belongsTo(ReksaDana::class, 'reksa_dana_id');
    }
}
