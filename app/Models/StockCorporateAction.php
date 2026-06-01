<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockCorporateAction extends Model
{
    protected $guarded = [];
    protected $casts = ['action_date' => 'date', 'value' => 'decimal:2'];
}
