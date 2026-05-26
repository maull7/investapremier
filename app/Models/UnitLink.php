<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UnitLink extends Model
{
    protected $fillable = [
        'unit_link', 'asuransi', 'jenis', 'tipe', 'mata_uang',
        'median_price', 'buy_price', 'sell_price', 'last_update',
    ];

    protected $casts = [
        'last_update' => 'date',
        'median_price' => 'decimal:4',
        'buy_price' => 'decimal:4',
        'sell_price' => 'decimal:4',
    ];

    public function harga(): HasMany
    {
        return $this->hasMany(HargaUnitLink::class, 'unit_link_id');
    }
}
