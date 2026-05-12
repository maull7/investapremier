<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MemberProfile extends Model
{
    protected $fillable = [
        'user_id', 'no_telepon', 'jenis_kelamin', 'kewarganegaraan',
        'agama', 'pekerjaan',
        'jenis_investasi', 'sumber_dana', 'tujuan_investasi', 'maksud_tujuan_lain',
        'rata_rata_penghasilan', 'pembukaan_rekening_efek', 'status',
    ];

    protected $casts = [
        'jenis_investasi'  => 'array',
        'sumber_dana'      => 'array',
        'tujuan_investasi' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function portfolios(): HasMany
    {
        return $this->hasMany(MemberPortfolio::class, 'user_id', 'user_id');
    }
}
