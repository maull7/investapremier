<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataSourceSyncLog extends Model
{
    protected $fillable = [
        'data_source_link_id', 'user_id', 'status', 'message',
        'rows_imported', 'file_path',
    ];

    public function link(): BelongsTo
    {
        return $this->belongsTo(DataSourceLink::class, 'data_source_link_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
