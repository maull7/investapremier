<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class YtmNormalCurve extends Model
{
    protected $table = 'ytm_normal_curves';

    protected $fillable = [
        'rating_id',
        'tenor_bulan',
        'ytm_normal',
    ];

    protected $casts = [
        'ytm_normal' => 'decimal:4',
    ];

    public function rating(): BelongsTo
    {
        return $this->belongsTo(RatingObligasi::class, 'rating_id');
    }
}
