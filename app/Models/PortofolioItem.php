<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortofolioItem extends Model
{
    protected $fillable = [
        'user_id',
        'perencanaan_investasi_id',
        'jenis',
        'produk_type',
        'produk_id',
        'nama_produk',
        'nominal',
        'harga_akuisisi',
        'nilai',
    ];

    protected $casts = [
        'nominal' => 'decimal:2',
        'harga_akuisisi' => 'decimal:2',
        'nilai' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function perencanaanInvestasi(): BelongsTo
    {
        return $this->belongsTo(PerencanaanInvestasi::class);
    }
}
