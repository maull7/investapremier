<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalisaPiutangBunga extends Model
{
    protected $table = 'analisa_reksadana_piutang_bunga';
    protected $fillable = [
        'analisa_reksa_dana_id', 'jenis_instrumen', 'jumlah',
    ];
    protected $casts = [
        'jumlah' => 'decimal:2',
    ];

    public function analisa(): BelongsTo
    {
        return $this->belongsTo(AnalisaReksaDana::class, 'analisa_reksa_dana_id');
    }
}
