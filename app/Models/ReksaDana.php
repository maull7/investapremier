<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReksaDana extends Model
{
    protected $table = 'reksa_dana';

    protected $fillable = [
        'nama_reksa_dana', 'nama_manajer_investasi', 'jenis',
        'kategori', 'mata_uang', 'nab_per_unit', 'tanggal_nab',
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

    public function getKategoriLabelAttribute(): string
    {
        return is_array($this->kategori) ? implode(', ', $this->kategori) : ($this->kategori ?? '—');
    }
}
