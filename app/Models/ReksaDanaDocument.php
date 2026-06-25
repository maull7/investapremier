<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class ReksaDanaDocument extends Model
{
    public const TYPE_LAPORAN_TAHUNAN = 'laporan_tahunan';
    public const TYPE_PROSPECTUS = 'prospektus';
    public const TYPE_FFS = 'ffs';

    protected $fillable = [
        'reksa_dana_id',
        'uploaded_by',
        'document_type',
        'ffs_month',
        'ffs_year',
        'original_name',
        'file_path',
        'mime_type',
        'file_size',
        'notes',
    ];

    public function reksaDana(): BelongsTo
    {
        return $this->belongsTo(ReksaDana::class, 'reksa_dana_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function parsedPages(): HasMany
    {
        return $this->hasMany(DocumentParsedPage::class, 'reksa_dana_document_id')->orderBy('page_parse');
    }

    public function partitions(): HasMany
    {
        return $this->hasMany(DocumentPartition::class, 'reksa_dana_document_id')->orderBy('start_page');
    }

    public function ffsExtractionResults(): HasMany
    {
        return $this->hasMany(FfsExtractionResult::class, 'reksa_dana_document_id')->orderByDesc('tanggal_data');
    }

    public function deleteStoredFile(): void
    {
        if ($this->file_path && Storage::disk('public')->exists($this->file_path)) {
            Storage::disk('public')->delete($this->file_path);
        }
    }

    protected static function booted(): void
    {
        static::deleting(function (ReksaDanaDocument $document) {
            $document->parsedPages()->delete();
            $document->partitions()->delete();
            $document->ffsExtractionResults()->delete();
        });
    }
}
