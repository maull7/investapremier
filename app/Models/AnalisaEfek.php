<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalisaEfek extends Model
{
    protected $table = 'analisa_efek';
    protected $fillable = [
        'analisa_reksa_dana_id', 'kode_efek', 'nama_efek', 'sektor',
        'bobot', 'bobot_seharusnya', 'kontribusi_kinerja', 'market_cap', 'harga_perolehan', 'persen_nab', 'top_10',
        'jumlah_lembar', 'harga_perolehan_rata_rata',
        'nilai_pasar', 'return_1m', 'return_3m', 'return_6m', 'return_1y',
        'ihsg_contribution', 'kontribusi_return', 'effect_type',
    ];
    protected $casts = [
        'top_10' => 'boolean',
        'nilai_pasar' => 'decimal:2',
        'harga_perolehan_rata_rata' => 'decimal:2',
        'return_1m' => 'decimal:4',
        'return_3m' => 'decimal:4',
        'return_6m' => 'decimal:4',
        'return_1y' => 'decimal:4',
        'ihsg_contribution' => 'decimal:4',
    ];

    public function analisa(): BelongsTo
    {
        return $this->belongsTo(AnalisaReksaDana::class, 'analisa_reksa_dana_id');
    }
}
