<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RatingObligasi extends Model
{
    protected $table = 'rating_obligasi';

    protected $fillable = [
        'kode',
        'nama',
        'keterangan',
        'urutan',
    ];

    public function ytmNormalCurves(): HasMany
    {
        return $this->hasMany(YtmNormalCurve::class, 'rating_id');
    }
}
