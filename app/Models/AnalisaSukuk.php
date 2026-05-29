<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalisaSukuk extends Model
{
    protected $table = 'analisa_reksadana_sukuk';
    protected $fillable = [
        'analisa_reksa_dana_id', 'kode_sukuk', 'nama_sukuk',
        'jenis_sukuk', 'bobot', 'yield', 'jatuh_tempo', 'rating',
    ];
    protected $casts = [
        'yield' => 'decimal:4',
    ];

    public function analisa(): BelongsTo
    {
        return $this->belongsTo(AnalisaReksaDana::class, 'analisa_reksa_dana_id');
    }
}
