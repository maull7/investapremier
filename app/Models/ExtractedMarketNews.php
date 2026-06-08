<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExtractedMarketNews extends Model
{
    protected $fillable = [
        'extraction_batch_id',
        'news_date',
        'title',
        'url',
        'source',
        'raw_payload',
    ];

    protected $casts = [
        'news_date' => 'datetime',
        'raw_payload' => 'array',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ExtractionBatch::class, 'extraction_batch_id');
    }
}
