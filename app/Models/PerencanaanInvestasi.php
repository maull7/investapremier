<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerencanaanInvestasi extends Model
{
    protected $table = 'perencanaan_investasi';

    protected $fillable = [
        'user_id',
        'kategori_perencanaan',
        'kebutuhan_dana',
        'target_waktu_tahun',
        'dana_tersedia',
        'investasi_per_bulan',
        'sumber_dana',
        'profil_risiko',
        'usia_anak',
        'target_pendidikan',
        'tipe_pendidikan',
        'lokasi_pendidikan',
        'estimasi_biaya_saat_ini',
        'pemenuhan_dana',
        'status',
        'ai_narasi',
        'ai_output',
    ];

    protected $casts = [
        'kebutuhan_dana' => 'decimal:2',
        'dana_tersedia' => 'decimal:2',
        'investasi_per_bulan' => 'decimal:2',
        'estimasi_biaya_saat_ini' => 'decimal:2',
        'pemenuhan_dana' => 'decimal:2',
        'ai_output' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function portofolioItems()
    {
        return $this->hasMany(PortofolioItem::class);
    }
}
