<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HargaUnitLink extends Model
{
    protected $table = 'harga_unit_links';

    protected $fillable = [
        'unit_link_id', 'datetime', 'harga_median', 'sell_buy_low', 'sell_buy_high',
    ];

    protected $casts = [
        'datetime'      => 'datetime',
        'harga_median'  => 'decimal:6',
        'sell_buy_low'  => 'decimal:6',
        'sell_buy_high' => 'decimal:6',
    ];

    public function unitLink(): BelongsTo
    {
        return $this->belongsTo(UnitLink::class, 'unit_link_id');
    }
}
