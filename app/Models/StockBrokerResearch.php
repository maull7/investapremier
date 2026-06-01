<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockBrokerResearch extends Model
{
    protected $table = 'stock_broker_researches';

    protected $guarded = [];
    protected $casts = ['research_date' => 'date', 'target_price' => 'decimal:2'];
}
