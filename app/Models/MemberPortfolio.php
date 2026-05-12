<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberPortfolio extends Model
{
    protected $fillable = ['user_id', 'jenis', 'nama_efek', 'mulai_kepemilikan', 'jumlah'];

    protected $casts = ['mulai_kepemilikan' => 'date'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
