<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalisaBank extends Model
{
    protected $table = 'analisa_bank';
    protected $fillable = [
        'analisa_reksa_dana_id', 'nama_bank', 'bobot',
        'car', 'npl', 'tingkat_bunga', 'jangka_waktu', 'persen_nab', 'klasifikasi_risiko',
        'jenis_bank', 'nilai_pasar', 'return_1m', 'return_3m', 'return_6m', 'return_1y',
    ];
    protected $casts = [
        'nilai_pasar' => 'decimal:2',
        'return_1m' => 'decimal:4',
        'return_3m' => 'decimal:4',
        'return_6m' => 'decimal:4',
        'return_1y' => 'decimal:4',
    ];

    public function analisa(): BelongsTo
    {
        return $this->belongsTo(AnalisaReksaDana::class, 'analisa_reksa_dana_id');
    }
}
