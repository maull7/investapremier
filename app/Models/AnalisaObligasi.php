<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalisaObligasi extends Model
{
    protected $table = 'analisa_obligasi';
    protected $fillable = [
        'analisa_reksa_dana_id', 'kode_obligasi', 'nama_obligasi',
        'bobot', 'durasi', 'rating',
    ];

    public function analisa(): BelongsTo
    {
        return $this->belongsTo(AnalisaReksaDana::class, 'analisa_reksa_dana_id');
    }
}
