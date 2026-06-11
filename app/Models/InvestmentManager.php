<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvestmentManager extends Model
{
    protected $fillable = [
        'name', 'kode_mi', 'kode_ojk', 'address', 'phone', 'email', 'website',
        'commissioner_president', 'commissioners', 'director_president',
        'directors', 'shareholders', 'investment_committee',
        'investment_management_team', 'last_updated_at', 'description',
        // pasardana fields
        'pasardana_id', 'fax', 'modal_dasar', 'modal_disetor',
        'izin_mi', 'izin_ppe', 'izin_pee',
    ];

    protected $casts = [
        'last_updated_at' => 'date',
        'pasardana_id'    => 'integer',
        'modal_dasar'     => 'decimal:2',
        'modal_disetor'   => 'decimal:2',
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

    public function personRoles()
    {
        return $this->hasMany(InvestmentPersonRole::class, 'investment_manager_id');
    }
}
