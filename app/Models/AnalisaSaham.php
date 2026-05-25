<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnalisaSaham extends Model
{
    protected $table = 'analisa_saham';

    protected $fillable = [
        'user_id',
        'kode_saham',
        'nama_perusahaan',
        'sektor',
        'mata_uang',
        'periode',
        'current_asset',
        'cash_equivalents',
        'account_receivable',
        'inventories',
        'other_current_asset',
        'fixed_asset',
        'other_non_current_asset',
        'total_asset',
        'current_liabilities',
        'account_payable',
        'accruals',
        'short_term_loans',
        'current_maturities_of_long_term_loans',
        'other_current_liabilities',
        'long_term_loans',
        'other_non_current_liabilities',
        'total_non_current_liabilities',
        'total_liabilities',
        'share_capital',
        'additional_paid_in_capital',
        'retained_earning',
        'others',
        'non_controlling_interest',
        'total_equity_equity_to_parent_entity',
        'equity',
        'net_revenue',
        'cost_of_good_sold',
        'gross_income',
        'operational_expense',
        'laba_operasional',
        'other_income_expense',
        'interest_expense',
        'income_before_tax',
        'taxes',
        'ebit',
        'ebitda',
        'net_income_attributable_to_non_controlling_interest',
        'net_income',
        'eps',
        'cash_flows_operating_activities',
        'cash_flows_investment',
        'cash_flows_financing',
        'catatan',
        'catatan_admin',
        'status',
        'ai_narasi',
        'ai_output',
        'ai_narasi_plus',
        'ai_output_plus',
        'pdf_path',
    ];

    protected $casts = [
        'current_asset' => 'decimal:2',
        'cash_equivalents' => 'decimal:2',
        'account_receivable' => 'decimal:2',
        'inventories' => 'decimal:2',
        'other_current_asset' => 'decimal:2',
        'fixed_asset' => 'decimal:2',
        'other_non_current_asset' => 'decimal:2',
        'total_asset' => 'decimal:2',
        'current_liabilities' => 'decimal:2',
        'account_payable' => 'decimal:2',
        'accruals' => 'decimal:2',
        'short_term_loans' => 'decimal:2',
        'current_maturities_of_long_term_loans' => 'decimal:2',
        'other_current_liabilities' => 'decimal:2',
        'long_term_loans' => 'decimal:2',
        'other_non_current_liabilities' => 'decimal:2',
        'total_non_current_liabilities' => 'decimal:2',
        'total_liabilities' => 'decimal:2',
        'share_capital' => 'decimal:2',
        'additional_paid_in_capital' => 'decimal:2',
        'retained_earning' => 'decimal:2',
        'others' => 'decimal:2',
        'non_controlling_interest' => 'decimal:2',
        'total_equity_equity_to_parent_entity' => 'decimal:2',
        'equity' => 'decimal:2',
        'net_revenue' => 'decimal:2',
        'cost_of_good_sold' => 'decimal:2',
        'gross_income' => 'decimal:2',
        'operational_expense' => 'decimal:2',
        'laba_operasional' => 'decimal:2',
        'other_income_expense' => 'decimal:2',
        'interest_expense' => 'decimal:2',
        'income_before_tax' => 'decimal:2',
        'taxes' => 'decimal:2',
        'ebit' => 'decimal:2',
        'ebitda' => 'decimal:2',
        'net_income_attributable_to_non_controlling_interest' => 'decimal:2',
        'net_income' => 'decimal:2',
        'eps' => 'decimal:2',
        'cash_flows_operating_activities' => 'decimal:2',
        'cash_flows_investment' => 'decimal:2',
        'cash_flows_financing' => 'decimal:2',
        'ai_output' => 'array',
        'ai_output_plus' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function brokerResearchDocuments(): HasMany
    {
        return $this->hasMany(AnalisaSahamBrokerResearchDocument::class)->latest();
    }

    protected static function booted(): void
    {
        static::deleting(function (AnalisaSaham $analisa) {
            $analisa->brokerResearchDocuments()->get()->each->deleteStoredFile();
        });
    }
}
