<?php

namespace App\Exports;

use App\Support\ExcelDateHelper;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AnalisaLapkeuTemplateExport implements FromArray, WithHeadings, WithStyles, WithColumnFormatting
{
    use ExcelDateHelper;

    public function headings(): array
    {
        return [
            'kode', 'nama_perusahaan', 'mata_uang', 'periode',
            'current_asset', 'cash_equivalents', 'account_receivable', 'inventories',
            'other_current_asset', 'fixed_asset', 'other_non_current_asset', 'total_asset',
            'current_liabilities', 'account_payable', 'accruals', 'short_term_loans',
            'current_maturities_of_long_term_loans', 'other_current_liabilities',
            'long_term_loans', 'other_non_current_liabilities',
            'total_non_current_liabilities', 'total_liabilities',
            'share_capital', 'additional_paid_in_capital', 'retained_earning', 'others',
            'non_controlling_interest', 'total_equity_equity_to_parent_entity', 'equity',
            'net_revenue', 'cost_of_good_sold', 'gross_income', 'operational_expense',
            'laba_operasional', 'other_income_expense', 'interest_expense', 'income_before_tax',
            'taxes', 'ebit', 'ebitda', 'net_income',
        ];
    }

    public function array(): array
    {
        return [
            [
                'BBCA', 'Bank Central Asia Tbk', 'IDR', $this->excelDateValue('2026-03-31'),
                100000000000, 50000000000, 30000000000, 20000000000,
                10000000000, 50000000000, 20000000000, 180000000000,
                40000000000, 15000000000, 5000000000, 10000000000,
                5000000000, 5000000000, 60000000000, 20000000000,
                80000000000, 120000000000,
                20000000000, 5000000000, 40000000000, 1000000000,
                1000000000, 56000000000, 60000000000,
                50000000000, 20000000000, 30000000000, 15000000000,
                15000000000, 2000000000, 1000000000, 13000000000,
                3000000000, 10000000000, 8000000000, 10000000000,
            ],
        ];
    }

    public function columnFormats(): array
    {
        return $this->dateColumnFormats(['D']);
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
