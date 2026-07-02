<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalisaLikuiditas extends Model
{
    protected $table = 'analisa_likuiditas';

    protected $fillable = [
        'analisa_reksa_dana_id', 'kategori', 'kode_efek', 'nama_efek',
        'rata_volume_transaksi_harian', 'volume_terendah', 'volume_saham',
        'skenario_20_persen_reds', 'skenario_reds_closing_10',
        'rasio_likuiditas_harian', 'rasio_likuiditas',
    ];

    protected $casts = [
        'rata_volume_transaksi_harian' => 'decimal:4',
        'volume_terendah' => 'decimal:4',
        'volume_saham' => 'decimal:4',
        'skenario_20_persen_reds' => 'decimal:4',
        'skenario_reds_closing_10' => 'decimal:4',
        'rasio_likuiditas_harian' => 'decimal:4',
        'rasio_likuiditas' => 'decimal:4',
    ];

    public function analisa(): BelongsTo
    {
        return $this->belongsTo(AnalisaReksaDana::class, 'analisa_reksa_dana_id');
    }
}
