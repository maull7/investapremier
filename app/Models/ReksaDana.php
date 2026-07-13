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
        // pasardana fields
        'pasardana_id',
        'isin_code',
        'is_etf',
        'is_index',
        'conservative_category',
        'dividend',
        'return_1d', 'return_1w', 'return_mtd', 'return_1m', 'return_3m',
        'return_6m', 'return_ytd', 'return_1y', 'return_3y', 'return_5y',
        'return_10y', 'return_inception',
        'annualized_return_1y', 'annualized_return_3y', 'annualized_return_5y',
        'annualized_return_10y',
        'stdev_1y', 'stdev_3y', 'stdev_5y', 'stdev_10y',
        'beta_1y', 'beta_3y', 'beta_5y', 'beta_10y',
        'sharpe_ratio_1y', 'sharpe_ratio_3y', 'sharpe_ratio_5y', 'sharpe_ratio_10y',
        'sortino_ratio_1y', 'sortino_ratio_3y', 'sortino_ratio_5y', 'sortino_ratio_10y',
        'treynor_ratio_1y', 'treynor_ratio_3y', 'treynor_ratio_5y', 'treynor_ratio_10y',
        'jensen_alpha_1y', 'jensen_alpha_3y', 'jensen_alpha_5y', 'jensen_alpha_10y',
        'tracking_error_1y', 'tracking_error_3y', 'tracking_error_5y', 'tracking_error_10y',
        'max_drawdown_1y', 'max_drawdown_3y', 'max_drawdown_5y', 'max_drawdown_10y',
        'aum', 'total_unit', 'expense_ratio', 'investment_manager_fee',
        'yearly_rating', 'one_year_rating', 'three_year_rating', 'five_year_rating',
        'ten_year_rating',
        'aum_published_date', 'aum_last_update', 'last_update',
        'last_fund_factsheet', 'last_updated_portfolio', 'expense_ratio_date',
        'parser_locks',
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
        // pasardana fields
        'pasardana_id'          => 'integer',
        'is_etf'                => 'boolean',
        'is_index'              => 'boolean',
        'dividend'              => 'boolean',
        'return_1d'             => 'decimal:6',
        'return_1w'             => 'decimal:6',
        'return_mtd'            => 'decimal:6',
        'return_1m'             => 'decimal:6',
        'return_3m'             => 'decimal:6',
        'return_6m'             => 'decimal:6',
        'return_ytd'            => 'decimal:6',
        'return_1y'             => 'decimal:6',
        'return_3y'             => 'decimal:6',
        'return_5y'             => 'decimal:6',
        'return_10y'            => 'decimal:6',
        'return_inception'      => 'decimal:6',
        'annualized_return_1y'  => 'decimal:6',
        'annualized_return_3y'  => 'decimal:6',
        'annualized_return_5y'  => 'decimal:6',
        'annualized_return_10y' => 'decimal:6',
        'aum'                   => 'decimal:2',
        'total_unit'            => 'decimal:2',
        'expense_ratio'         => 'decimal:6',
        'yearly_rating'         => 'integer',
        'one_year_rating'       => 'integer',
        'three_year_rating'     => 'integer',
        'five_year_rating'      => 'integer',
        'ten_year_rating'       => 'integer',
        'aum_published_date'    => 'date',
        'aum_last_update'       => 'date',
        'last_update'           => 'date',
        'last_fund_factsheet'   => 'date',
        'last_updated_portfolio' => 'date',
        'expense_ratio_date'    => 'date',
        'parser_locks'          => 'array',
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

    public function analisa(): HasMany
    {
        return $this->hasMany(AnalisaReksaDana::class, 'reksa_dana_id');
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

    public function personRoles(): HasMany
    {
        return $this->hasMany(InvestmentPersonRole::class, 'reksa_dana_id');
    }

    public function fillFromKode(): bool
    {
        if (empty($this->kode_reksa_dana)) return false;

        $parsed = app(KodeReksaDanaParser::class)->databaseAttributes($this->kode_reksa_dana);
        if (!$parsed) return false;

        $needsFill = false;
        $fillData = [];

        foreach (['nama_manajer_investasi', 'jenis', 'kategori_produk', 'kelas', 'mata_uang'] as $field) {
            if (empty($this->{$field}) && isset($parsed[$field]) && !empty($parsed[$field])) {
                $fillData[$field] = $parsed[$field];
                $needsFill = true;
            }
        }

        if (!empty($parsed['kategori']) && (empty($this->kategori) || !is_array($this->kategori) || count($this->kategori) === 0)) {
            $fillData['kategori'] = $parsed['kategori'];
            $needsFill = true;
        }

        if (!$needsFill) return false;

        $this->update($fillData);

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
