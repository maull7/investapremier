<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ObligasiHargaReferensi extends Model
{
    protected $table = 'obligasi_harga_referensi';

    protected $fillable = [
        'kode', 'nama', 'tanggal_terbit', 'emiten', 'sektor', 'sub_sektor',
        'industri', 'sub_industri', 'denominasi', 'rating', 'syariah',
        'kupon', 'jatuh_tempo', 'harga_persen', 'ttm', 'ytm',
        'current_yield', 'total_val', 'outstanding_amount',
    ];

    protected $casts = [
        'tanggal_terbit' => 'date',
        'jatuh_tempo' => 'date',
        'syariah' => 'boolean',
        'kupon' => 'decimal:4',
        'harga_persen' => 'decimal:4',
        'ttm' => 'decimal:6',
        'ytm' => 'decimal:6',
        'current_yield' => 'decimal:4',
        'total_val' => 'decimal:2',
        'outstanding_amount' => 'decimal:2',
    ];
}
