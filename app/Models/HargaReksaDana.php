<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HargaReksaDana extends Model
{
    protected $table = 'harga_reksa_dana';

    protected $fillable = [
        'reksa_dana_id',
        'tanggal',
        'nab_per_unit',
        'aum',
        'unit_participation',
    ];

    protected $casts = [
        'tanggal'            => 'date',
        'nab_per_unit'       => 'decimal:6',
        'aum'                => 'decimal:2',
        'unit_participation' => 'decimal:2',
    ];

    public function reksaDana(): BelongsTo
    {
        return $this->belongsTo(ReksaDana::class, 'reksa_dana_id');
    }
}
