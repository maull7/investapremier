<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class AnalisaSahamBrokerResearchDocument extends Model
{
    protected $fillable = [
        'analisa_saham_id',
        'uploaded_by',
        'broker',
        'original_name',
        'file_path',
        'mime_type',
        'file_size',
    ];

    public function analisaSaham(): BelongsTo
    {
        return $this->belongsTo(AnalisaSaham::class);
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
