<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExtractionBatch extends Model
{
    protected $fillable = [
        'data_type',
        'source',
        'data_date',
        'identifier',
        'range_start',
        'range_end',
        'range_label',
        'status',
        'total_records',
        'error_message',
        'created_by',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'data_date' => 'date',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'range_start' => 'integer',
        'range_end' => 'integer',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function stockDailyTransactions(): HasMany
    {
        return $this->hasMany(ExtractedStockDailyTransaction::class);
    }

    public function bondData(): HasMany
    {
        return $this->hasMany(ExtractedBondData::class);
    }

    public function marketNews(): HasMany
    {
        return $this->hasMany(ExtractedMarketNews::class);
    }
}
