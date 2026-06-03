<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Stock extends Model
{
    protected $fillable = [
        'kode', 'nama', 'sektor', 'sub_industri',
        'harga_terbaru', 'harga_penutupan_sebelumnya',
        'harga_pembukaan', 'harga_tertinggi', 'harga_terendah',
        'volume', 'value', 'frekuensi', 'jumlah_saham',
        'market_capital', 'last_update',
    ];

    protected $casts = [
        'harga_terbaru' => 'decimal:2',
        'harga_penutupan_sebelumnya' => 'decimal:2',
        'harga_pembukaan' => 'decimal:2',
        'harga_tertinggi' => 'decimal:2',
        'harga_terendah' => 'decimal:2',
        'value' => 'decimal:2',
        'market_capital' => 'decimal:2',
        'last_update' => 'date',
    ];

    public function profile(): HasOne
    {
        return $this->hasOne(StockProfile::class);
    }

    public function corporateActions(): HasMany
    {
        return $this->hasMany(StockCorporateAction::class)->latest('action_date');
    }

    public function prices(): HasMany
    {
        return $this->hasMany(StockPrice::class)->oldest('tanggal');
    }

    public function financialReports(): HasMany
    {
        return $this->hasMany(StockFinancialReport::class)->latest('report_year');
    }

    public function news(): HasMany
    {
        return $this->hasMany(StockNews::class)->latest('published_at');
    }

    public function brokerResearches(): HasMany
    {
        return $this->hasMany(StockBrokerResearch::class)->latest('research_date');
    }

    public function brokerDocuments(): HasMany
    {
        return $this->hasMany(StockBrokerDocument::class)->latest('tanggal');
    }
}
