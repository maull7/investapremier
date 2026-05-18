<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataSourceLinkUrl extends Model
{
    protected $fillable = [
        'data_source_link_id', 'label', 'url', 'sort_order',
    ];

    public function link(): BelongsTo
    {
        return $this->belongsTo(DataSourceLink::class, 'data_source_link_id');
    }
}
