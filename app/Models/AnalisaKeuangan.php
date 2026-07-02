<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalisaKeuangan extends Model
{
    protected $table = 'analisa_keuangan';

    protected $fillable = [
        'analisa_reksa_dana_id', 'kategori', 'kode_efek', 'nama_efek',
        'per', 'pbv', 'roe', 'roa', 'npm', 'ev_ebitda',
        'der', 'current_ratio', 'aktivitas_lancar', 'gross_profit_margin', 'operating_profit_margin',
        'ytm', 'rating', 'kupon', 'tenor', 'durasi', 'shadow_rating',
        'npl', 'car', 'ldr', 'nim', 'cir',
    ];

    protected $casts = [
        'per' => 'decimal:4',
        'pbv' => 'decimal:4',
        'roe' => 'decimal:4',
        'roa' => 'decimal:4',
        'npm' => 'decimal:4',
        'ev_ebitda' => 'decimal:4',
        'der' => 'decimal:4',
        'current_ratio' => 'decimal:4',
        'aktivitas_lancar' => 'decimal:4',
        'gross_profit_margin' => 'decimal:4',
        'operating_profit_margin' => 'decimal:4',
        'ytm' => 'decimal:4',
        'kupon' => 'decimal:4',
        'tenor' => 'decimal:4',
        'durasi' => 'decimal:4',
        'npl' => 'decimal:4',
        'car' => 'decimal:4',
        'ldr' => 'decimal:4',
        'nim' => 'decimal:4',
        'cir' => 'decimal:4',
    ];

    public function analisa(): BelongsTo
    {
        return $this->belongsTo(AnalisaReksaDana::class, 'analisa_reksa_dana_id');
    }
}
