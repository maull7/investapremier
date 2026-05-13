<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalisaEfek extends Model
{
    protected $table = 'analisa_efek';
    protected $fillable = [
        'analisa_reksa_dana_id', 'kode_efek', 'nama_efek', 'sektor',
        'bobot', 'kontribusi_kinerja', 'market_cap', 'top_10',
    ];
    protected $casts = ['top_10' => 'boolean'];

    public function analisa(): BelongsTo
    {
        return $this->belongsTo(AnalisaReksaDana::class, 'analisa_reksa_dana_id');
    }
}
