<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentPartition extends Model
{
    protected $table = 'document_partitions';

    protected $fillable = [
        'reksa_dana_document_id',
        'created_by',
        'nama_partisi',
        'start_page',
        'end_page',
        'start_page_pdf',
        'end_page_pdf',
        'source',
    ];

    protected $attributes = [
        'source' => 'manual',
    ];

    protected $casts = [
        'start_page'     => 'integer',
        'end_page'       => 'integer',
        'start_page_pdf' => 'integer',
        'end_page_pdf'   => 'integer',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(ReksaDanaDocument::class, 'reksa_dana_document_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
