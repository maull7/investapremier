<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExtractedBondData extends Model
{
    protected $fillable = [
        'extraction_batch_id',
        'bond_code',
        'bond_name',
        'issuer',
        'maturity_date',
        'coupon',
        'rating',
        'yield',
        'fair_price',
        'data_date',
        'source',
        'raw_payload',
    ];

    protected $casts = [
        'maturity_date' => 'date',
        'coupon' => 'decimal:4',
        'yield' => 'decimal:6',
        'fair_price' => 'decimal:4',
        'data_date' => 'date',
        'raw_payload' => 'array',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ExtractionBatch::class, 'extraction_batch_id');
    }
}
