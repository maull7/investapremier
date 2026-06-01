<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockNews extends Model
{
    protected $table = 'stock_news';
    protected $guarded = [];
    protected $casts = ['published_at' => 'datetime'];
}
