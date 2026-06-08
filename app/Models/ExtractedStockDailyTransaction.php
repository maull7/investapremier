<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExtractedStockDailyTransaction extends Model
{
    protected $fillable = [
        'extraction_batch_id',
        'stock_code',
        'data_date',
        'open',
        'high',
        'low',
        'close',
        'volume',
        'price_change',
        'change_percent',
        'market_cap',
        'source',
        'raw_payload',
    ];

    protected $casts = [
        'data_date' => 'date',
        'open' => 'decimal:2',
        'high' => 'decimal:2',
        'low' => 'decimal:2',
        'close' => 'decimal:2',
        'price_change' => 'decimal:2',
        'change_percent' => 'decimal:4',
        'market_cap' => 'decimal:2',
        'raw_payload' => 'array',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ExtractionBatch::class, 'extraction_batch_id');
    }
}
