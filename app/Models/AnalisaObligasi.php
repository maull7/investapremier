<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalisaObligasi extends Model
{
    protected $table = 'analisa_obligasi';
    protected $fillable = [
        'analisa_reksa_dana_id', 'kode_obligasi', 'nama_obligasi',
        'bobot', 'durasi', 'rating', 'ytm', 'kupon', 'tanggal_jatuh_tempo', 'penerbit', 'persen_nab',
        'nilai_pasar', 'nilai_nominal', 'harga_perolehan_rata_rata', 'suku_bunga',
        'return_1m', 'return_3m', 'return_6m', 'return_1y',
    ];
    protected $casts = [
        'nilai_pasar' => 'decimal:2',
        'nilai_nominal' => 'decimal:2',
        'harga_perolehan_rata_rata' => 'decimal:4',
        'suku_bunga' => 'decimal:4',
        'return_1m' => 'decimal:4', 'return_3m' => 'decimal:4',
        'return_6m' => 'decimal:4', 'return_1y' => 'decimal:4',
        'tanggal_jatuh_tempo' => 'date',
    ];

    public function analisa(): BelongsTo
    {
        return $this->belongsTo(AnalisaReksaDana::class, 'analisa_reksa_dana_id');
    }
}
