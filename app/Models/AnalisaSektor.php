<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalisaSektor extends Model
{
    protected $table = 'analisa_sektor';
    protected $fillable = ['analisa_reksa_dana_id', 'nama_sektor', 'bobot'];

    public function analisa(): BelongsTo
    {
        return $this->belongsTo(AnalisaReksaDana::class, 'analisa_reksa_dana_id');
    }
}
