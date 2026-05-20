<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UnitLinkTemplateExport implements FromArray, WithHeadings, WithStyles
{
    public function headings(): array
    {
        return [
            'Unit Link',
            'Asuransi',
            'Jenis',
            'Tipe',
            'Mata Uang',
            'Median Price',
            'Buy Price',
            'Sell Price',
            'Last Update',
        ];
    }

    public function array(): array
    {
        return [
            [
                'AFI Dynamic Money Rp',
                'AFI', 'Saham', 'Konvensional', 'IDR',
                1145.6496, 1117.7069, 1173.5922,
                '18-May-2026',
            ],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
