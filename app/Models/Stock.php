<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    protected $fillable = [
        'kode', 'nama', 'sektor', 'sub_industri',
        'harga_terbaru', 'harga_penutupan_sebelumnya',
        'harga_pembukaan', 'harga_tertinggi', 'harga_terendah',
        'volume', 'value', 'frekuensi', 'jumlah_saham',
        'market_capital', 'last_update',
    ];

    protected $casts = [
        'harga_terbaru' => 'decimal:2',
        'harga_penutupan_sebelumnya' => 'decimal:2',
        'harga_pembukaan' => 'decimal:2',
        'harga_tertinggi' => 'decimal:2',
        'harga_terendah' => 'decimal:2',
        'value' => 'decimal:2',
        'market_capital' => 'decimal:2',
        'last_update' => 'date',
    ];
}
