<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvestmentManager extends Model
{
    protected $fillable = ['name'];

    public function periods()
    {
        return $this->hasMany(InvestmentManagerPeriod::class);
    }
}
