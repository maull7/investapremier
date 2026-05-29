<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EffectSector extends Model
{
    protected $table = 'effect_sectors';

    protected $fillable = [
        'effect_code',
        'effect_name',
        'sector_name',
        'effect_type',
        'source',
    ];
}
