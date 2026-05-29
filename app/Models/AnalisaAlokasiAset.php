<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalisaAlokasiAset extends Model
{
    protected $table = 'analisa_alokasi_aset';

    protected $fillable = [
        'analisa_reksa_dana_id',
        'nama_aset',
        'persentase',
    ];

    public function analisa(): BelongsTo
    {
        return $this->belongsTo(AnalisaReksaDana::class, 'analisa_reksa_dana_id');
    }
}
