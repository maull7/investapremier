<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalisaBank extends Model
{
    protected $table = 'analisa_bank';
    protected $fillable = [
        'analisa_reksa_dana_id', 'nama_bank', 'bobot',
        'car', 'npl', 'klasifikasi_risiko',
    ];

    public function analisa(): BelongsTo
    {
        return $this->belongsTo(AnalisaReksaDana::class, 'analisa_reksa_dana_id');
    }
}
