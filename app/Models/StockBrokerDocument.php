<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockBrokerDocument extends Model
{
    protected $guarded = [];

    protected $casts = ['tanggal' => 'date'];

    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }
}
