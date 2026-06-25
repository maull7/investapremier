<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FfsExtractionResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'reksa_dana_document_id',
        'reksa_dana_id',
        'created_by',
        'ffs_month',
        'ffs_year',
        'tanggal_data',
        'extracted_data',
    ];

    protected $casts = [
        'ffs_month'      => 'integer',
        'ffs_year'       => 'integer',
        'tanggal_data'   => 'date',
        'extracted_data' => 'array',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(ReksaDanaDocument::class, 'reksa_dana_document_id');
    }

    public function reksaDana(): BelongsTo
    {
        return $this->belongsTo(ReksaDana::class, 'reksa_dana_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
