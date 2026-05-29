<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReksaDana extends Model
{
    protected $table = 'reksa_dana';

    protected $fillable = [
        'kode_reksa_dana',
        'nama_reksa_dana',
        'nama_manajer_investasi',
        'jenis',
        'kategori',
        'kategori_produk',
        'benchmark',
        'tujuan_investasi',
        'kebijakan_investasi',
        'mata_uang',
        'nab_per_unit',
        'tanggal_nab',
    ];

    protected $casts = [
        'kategori'    => 'array',
        'tanggal_nab' => 'date',
        'nab_per_unit' => 'decimal:6',
    ];

    public function harga(): HasMany
    {
        return $this->hasMany(HargaReksaDana::class, 'reksa_dana_id');
    }

    public function dataSourceLinks(): HasMany
    {
        return $this->hasMany(DataSourceLink::class, 'reksa_dana_id');
    }

    public function getKategoriLabelAttribute(): string
    {
        return is_array($this->kategori) ? implode(', ', $this->kategori) : ($this->kategori ?? '—');
    }
}
