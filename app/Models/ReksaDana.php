<?php

namespace App\Models;

use App\Services\KodeReksaDanaParser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReksaDana extends Model
{
    protected $table = 'reksa_dana';

    protected $appends = [
        'display_kelas',
        'display_mata_uang',
    ];

    protected $fillable = [
        'kode_reksa_dana',
        'investment_manager_id',
        'nama_reksa_dana',
        'nama_manajer_investasi',
        'jenis',
        'kategori',
        'kategori_produk',
        'kelas',
        'benchmark',
        'tujuan_investasi',
        'kebijakan_investasi',
        'description',
        'custodian_bank',
        'launch_date',
        'mata_uang',
        'risk_category',
        'subscription_fee',
        'redemption_fee',
        'switching_fee',
        'management_fee',
        'custodian_fee',
        'minimum_subscription',
        'minimum_topup',
        'minimum_redemption',
        'nab_per_unit',
        'tanggal_nab',
    ];

    protected $casts = [
        'kategori'              => 'array',
        'tanggal_nab'           => 'date',
        'launch_date'           => 'date',
        'nab_per_unit'          => 'decimal:6',
        'subscription_fee'      => 'decimal:2',
        'redemption_fee'        => 'decimal:2',
        'switching_fee'         => 'decimal:2',
        'management_fee'        => 'decimal:2',
        'custodian_fee'         => 'decimal:2',
        'minimum_subscription'  => 'decimal:2',
        'minimum_topup'         => 'decimal:2',
        'minimum_redemption'    => 'decimal:2',
    ];

    public function investmentManager(): BelongsTo
    {
        return $this->belongsTo(InvestmentManager::class, 'investment_manager_id');
    }

    public function harga(): HasMany
    {
        return $this->hasMany(HargaReksaDana::class, 'reksa_dana_id');
    }

    public function dataSourceLinks(): HasMany
    {
        return $this->hasMany(DataSourceLink::class, 'reksa_dana_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ReksaDanaDocument::class, 'reksa_dana_id')->latest();
    }

    public function assetAllocations(): HasMany
    {
        return $this->hasMany(MutualFundAssetAllocation::class, 'reksa_dana_id');
    }

    public function portfolioCompositions(): HasMany
    {
        return $this->hasMany(MutualFundPortfolioComposition::class, 'reksa_dana_id');
    }

    public function managementTeams(): HasMany
    {
        return $this->hasMany(MutualFundManagementTeam::class, 'reksa_dana_id');
    }

    public function fillFromKode(): bool
    {
        if (empty($this->kode_reksa_dana)) return false;

        $needsFill = empty($this->nama_manajer_investasi)
            || empty($this->jenis)
            || empty($this->kategori_produk)
            || empty($this->kelas);

        if (!$needsFill) return false;

        $parsed = app(KodeReksaDanaParser::class)->databaseAttributes($this->kode_reksa_dana);
        if (!$parsed) return false;

        $this->update($parsed);

        return true;
    }

    public function getKodeParsedAttribute(): array
    {
        return app(KodeReksaDanaParser::class)->parse((string) $this->kode_reksa_dana);
    }

    public function getDisplayKelasAttribute(): string
    {
        return app(KodeReksaDanaParser::class)->resolveClassName($this->kelas, (string) $this->kode_reksa_dana);
    }

    public function getDisplayMataUangAttribute(): string
    {
        return app(KodeReksaDanaParser::class)->resolveCurrencyName($this->mata_uang, (string) $this->kode_reksa_dana);
    }

    public function getKategoriLabelAttribute(): string
    {
        return is_array($this->kategori) ? implode(', ', $this->kategori) : ($this->kategori ?? '—');
    }

    protected static function booted(): void
    {
        static::deleting(function (ReksaDana $reksaDana) {
            $reksaDana->documents()->get()->each->deleteStoredFile();
        });
    }
}
