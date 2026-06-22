<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalisaSukuk extends Model
{
    protected $table = 'analisa_reksadana_sukuk';
    protected $fillable = [
        'analisa_reksa_dana_id', 'kode_sukuk', 'nama_sukuk',
        'jenis_sukuk', 'bobot', 'yield', 'jatuh_tempo', 'rating', 'persen_nab',
        'nilai_nominal', 'harga_perolehan_rata_rata', 'nilai_wajar', 'tingkat_bagi_hasil',
    ];
    protected $casts = [
        'yield' => 'decimal:4',
        'nilai_nominal' => 'decimal:2',
        'harga_perolehan_rata_rata' => 'decimal:4',
        'nilai_wajar' => 'decimal:2',
        'tingkat_bagi_hasil' => 'decimal:4',
    ];

    public function analisa(): BelongsTo
    {
        return $this->belongsTo(AnalisaReksaDana::class, 'analisa_reksa_dana_id');
    }
}
