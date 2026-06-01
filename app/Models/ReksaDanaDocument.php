<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ReksaDanaDocument extends Model
{
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

    public function deleteStoredFile(): void
    {
        if ($this->file_path && Storage::disk('public')->exists($this->file_path)) {
            Storage::disk('public')->delete($this->file_path);
        }
    }
}
