<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InvestmentManagerTemplateExport implements FromArray, WithHeadings, WithStyles
{
    public function headings(): array
    {
        return [
            'nama_investment_manager',
            'aum 30 Apr 2026',
            'up 30 Apr 2026',
            'aum 31 May 2026',
            'up 31 May 2026',
            'aum 30 Jun 2026',
            'up 30 Jun 2026',
        ];
    }

    public function array(): array
    {
        return [
            [
                'Allianz Global Investors Asset Management Indonesia, PT',
                4145645132526, 3971685648.74,
                null, null,
                null, null,
            ],
            [
                'Alpha Aset Manajemen, PT',
                10904451518, 9901468.63,
                null, null,
                null, null,
            ],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
