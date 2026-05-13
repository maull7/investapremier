<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalisaKinerjaBulanan extends Model
{
    protected $table = 'analisa_kinerja_bulanan';
    protected $fillable = ['analisa_reksa_dana_id', 'periode', 'return_pct'];
    protected $casts = ['periode' => 'date'];

    public function analisa(): BelongsTo
    {
        return $this->belongsTo(AnalisaReksaDana::class, 'analisa_reksa_dana_id');
    }
}
