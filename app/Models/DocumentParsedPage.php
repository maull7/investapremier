<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentParsedPage extends Model
{
    protected $table = 'document_parsed_pages';

    protected $fillable = [
        'reksa_dana_document_id',
        'page_pdf',
        'page_parse',
        'text_content',
    ];

    protected $casts = [
        'page_pdf'   => 'integer',
        'page_parse' => 'integer',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(ReksaDanaDocument::class, 'reksa_dana_document_id');
    }
}
