<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgressCheckin extends Model
{
    protected $fillable = [
        'perencanaan_investasi_id',
        'user_id',
        'dana_terkumpul',
        'catatan',
        'tanggal_checkin',
    ];

    protected $casts = [
        'dana_terkumpul' => 'decimal:2',
        'tanggal_checkin' => 'date',
    ];

    public function perencanaanInvestasi()
    {
        return $this->belongsTo(PerencanaanInvestasi::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
