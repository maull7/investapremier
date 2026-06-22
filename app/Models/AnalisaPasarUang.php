<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalisaPasarUang extends Model
{
    protected $table = 'analisa_reksadana_pasar_uang';
    protected $fillable = [
        'analisa_reksa_dana_id', 'nama_instrumen', 'jenis_instrumen',
        'nilai_tercatat', 'suku_bunga', 'jatuh_tempo', 'persen_nab',
    ];
    protected $casts = [
        'nilai_tercatat' => 'decimal:2',
        'suku_bunga' => 'decimal:4',
        'jatuh_tempo' => 'date',
    ];

    public function analisa(): BelongsTo
    {
        return $this->belongsTo(AnalisaReksaDana::class, 'analisa_reksa_dana_id');
    }
}
