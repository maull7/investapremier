<?php

namespace App\Imports;

use App\Models\ObligasiBond;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Illuminate\Support\Collection;

class ObligasiBondImport implements ToCollection, WithHeadingRow, WithCalculatedFormulas
{
    public int $imported = 0;

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            if (empty(trim($row['kode'] ?? ''))) continue;

            $toNum = fn($v) => is_numeric($v) ? $v : (is_numeric(str_replace(',', '', $v)) ? str_replace(',', '', $v) : null);

            ObligasiBond::updateOrCreate(
                [
                    'kode' => strtoupper(trim($row['kode'])),
                    'periode' => $row['periode'] ?? null,
                ],
                [
                    'current_asset' => $toNum($row['current_asset'] ?? null),
                    'current_liabilities' => $toNum($row['current_liabilities'] ?? null),
                    'total_asset' => $toNum($row['total_asset'] ?? null),
                    'total_liabilities' => $toNum($row['total_liabilities'] ?? null),
                    'retained_earning' => $toNum($row['retained_earning'] ?? null),
                    'equity' => $toNum($row['equity'] ?? null),
                    'interest_expense' => $toNum($row['interest_expense'] ?? null),
                    'laba_operasional' => $toNum($row['laba_operasional'] ?? null),
                    'cash_equivalents' => $toNum($row['cash_equivalents'] ?? null),
                    'account_receivable' => $toNum($row['account_receivable'] ?? null),
                    'inventories' => $toNum($row['inventories'] ?? null),
                    'other_current_asset' => $toNum($row['other_current_asset'] ?? null),
                    'fixed_asset' => $toNum($row['fixed_asset'] ?? null),
                    'other_non_current_asset' => $toNum($row['other_non_current_asset'] ?? null),
                    'account_payable' => $toNum($row['account_payable'] ?? null),
                    'accruals' => $toNum($row['accruals'] ?? null),
                    'short_term_loans' => $toNum($row['short_term_loans'] ?? null),
                    'current_maturities_of_long_term_loans' => $toNum($row['current_maturities_of_long_term_loans'] ?? null),
                    'other_current_liabilities' => $toNum($row['other_current_liabilities'] ?? null),
                    'long_term_loans' => $toNum($row['long_term_loans'] ?? null),
                    'employee_benefits' => $toNum($row['employee_benefits'] ?? null),
                    'other_non_current_liabilities' => $toNum($row['other_non_current_liabilities'] ?? null),
                    'total_non_current_liabilities' => $toNum($row['total_non_current_liabilities'] ?? null),
                    'share_capital' => $toNum($row['share_capital'] ?? null),
                    'additional_paid_in_capital' => $toNum($row['additional_paid_in_capital'] ?? null),
                    'others' => $toNum($row['others'] ?? null),
                    'non_controlling_interest' => $toNum($row['non_controlling_interest'] ?? null),
                    'total_equity_equity_to_parent_entity' => $toNum($row['total_equity_equity_to_parent_entity'] ?? null),
                    'net_revenue' => $toNum($row['net_revenue'] ?? null),
                    'cost_of_good_sold' => $toNum($row['cost_of_good_sold'] ?? null),
                    'gross_income' => $toNum($row['gross_income'] ?? null),
                    'operational_expense' => $toNum($row['operational_expense'] ?? null),
                    'other_income_expense' => $toNum($row['other_income_expense'] ?? null),
                    'income_before_tax' => $toNum($row['income_before_tax'] ?? null),
                    'taxes' => $toNum($row['taxes'] ?? null),
                    'ebit' => $toNum($row['ebit'] ?? null),
                    'ebitda' => $toNum($row['ebitda'] ?? null),
                    'net_income_attributable_to_non_controlling_interest' => $toNum($row['net_income_attributable_to_non_controlling_interest'] ?? null),
                    'net_income' => $toNum($row['net_income'] ?? null),
                    'cash_flows_operating_activities' => $toNum($row['cash_flows_operating_activities'] ?? null),
                    'cash_flows_investment' => $toNum($row['cash_flows_investment'] ?? null),
                    'cash_flows_financing' => $toNum($row['cash_flows_financing'] ?? null),
                ]
            );

            $this->imported++;
        }
    }
}
