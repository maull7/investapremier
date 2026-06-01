<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvestmentManager extends Model
{
    protected $fillable = [
        'name', 'kode_mi', 'kode_ojk', 'address', 'phone', 'email', 'website',
        'commissioner_president', 'commissioners', 'director_president',
        'directors', 'shareholders', 'last_updated_at', 'description',
    ];

    protected $casts = [
        'last_updated_at' => 'date',
    ];

    public function periods()
    {
        return $this->hasMany(InvestmentManagerPeriod::class);
    }

    public function funds()
    {
        return $this->hasMany(ReksaDana::class, 'nama_manajer_investasi', 'name');
    }

    public function products()
    {
        return $this->funds();
    }
}
