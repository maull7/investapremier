<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LapkeuPdfExtraction extends Model
{
    protected $fillable = [
        'uuid',
        'user_id',
        'instrumen',
        'status',
        'file_path',
        'original_name',
        'file_size',
        'result_data',
        'error_message',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'result_data' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
