<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ObligasiBondTemplateExport implements FromArray, WithHeadings, WithStyles
{
    public function headings(): array
    {
        return [
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
    }

    public function array(): array
    {
        return [
            [
                'PJAA', '202603',
                774167000000, 321666000000, 3526454000000, 1704820000000,
                1567845000000, 1821634000000, -70012000000, 293050000000,
                398551000000, 30667000000, 7103000000, 337846000000,
                2381572000000, 370715000000, 14866000000, 197381000000,
                0, 86425000000, 22994000000, 740685000000,
                184023000000, 458446000000, 1383154000000, 400000000000,
                40404000000, -206351000000, 19736000000, 1801898000000,
                207575000000, -151223000000, 56352000000, -70742000000,
                -5485000000, -36773000000, -1657000000, -19875000000,
                53297000000, 374000000, -38804000000, 194670000000,
                -6290000000, -68350000000,
            ],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
