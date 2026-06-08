<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PheiCreditSpreadMatrix extends Model
{
    protected $fillable = [
        'data_date',
        'tenor_bulan',
        'rating_aaa',
        'rating_aa',
        'rating_a',
        'rating_bbb',
        'source',
    ];

    protected $casts = [
        'data_date' => 'date',
        'rating_aaa' => 'decimal:6',
        'rating_aa' => 'decimal:6',
        'rating_a' => 'decimal:6',
        'rating_bbb' => 'decimal:6',
    ];
}
