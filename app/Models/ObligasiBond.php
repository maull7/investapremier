<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ObligasiBond extends Model
{
    protected $table = 'obligasi_bonds';

    protected $fillable = [
        'kode', 'periode',
        'current_asset', 'current_liabilities', 'total_asset', 'total_liabilities',
        'retained_earning', 'equity', 'interest_expense', 'laba_operasional',
        'cash_equivalents', 'account_receivable', 'inventories', 'other_current_asset',
        'fixed_asset', 'other_non_current_asset', 'account_payable', 'accruals',
        'short_term_loans', 'current_maturities_of_long_term_loans', 'other_current_liabilities',
        'long_term_loans', 'employee_benefits', 'other_non_current_liabilities',
        'total_non_current_liabilities', 'share_capital', 'additional_paid_in_capital',
        'others', 'non_controlling_interest', 'total_equity_equity_to_parent_entity',
        'net_revenue', 'cost_of_good_sold', 'gross_income', 'operational_expense',
        'other_income_expense', 'income_before_tax', 'taxes', 'ebit', 'ebitda',
        'net_income_attributable_to_non_controlling_interest', 'net_income',
        'cash_flows_operating_activities', 'cash_flows_investment', 'cash_flows_financing',
    ];

    protected $casts = [
        'current_asset' => 'decimal:2',
        'current_liabilities' => 'decimal:2',
        'total_asset' => 'decimal:2',
        'total_liabilities' => 'decimal:2',
        'retained_earning' => 'decimal:2',
        'equity' => 'decimal:2',
        'interest_expense' => 'decimal:2',
        'laba_operasional' => 'decimal:2',
        'cash_equivalents' => 'decimal:2',
        'account_receivable' => 'decimal:2',
        'inventories' => 'decimal:2',
        'other_current_asset' => 'decimal:2',
        'fixed_asset' => 'decimal:2',
        'other_non_current_asset' => 'decimal:2',
        'account_payable' => 'decimal:2',
        'accruals' => 'decimal:2',
        'short_term_loans' => 'decimal:2',
        'current_maturities_of_long_term_loans' => 'decimal:2',
        'other_current_liabilities' => 'decimal:2',
        'long_term_loans' => 'decimal:2',
        'employee_benefits' => 'decimal:2',
        'other_non_current_liabilities' => 'decimal:2',
        'total_non_current_liabilities' => 'decimal:2',
        'share_capital' => 'decimal:2',
        'additional_paid_in_capital' => 'decimal:2',
        'others' => 'decimal:2',
        'non_controlling_interest' => 'decimal:2',
        'total_equity_equity_to_parent_entity' => 'decimal:2',
        'net_revenue' => 'decimal:2',
        'cost_of_good_sold' => 'decimal:2',
        'gross_income' => 'decimal:2',
        'operational_expense' => 'decimal:2',
        'other_income_expense' => 'decimal:2',
        'income_before_tax' => 'decimal:2',
        'taxes' => 'decimal:2',
        'ebit' => 'decimal:2',
        'ebitda' => 'decimal:2',
        'net_income_attributable_to_non_controlling_interest' => 'decimal:2',
        'net_income' => 'decimal:2',
        'cash_flows_operating_activities' => 'decimal:2',
        'cash_flows_investment' => 'decimal:2',
        'cash_flows_financing' => 'decimal:2',
    ];
}
