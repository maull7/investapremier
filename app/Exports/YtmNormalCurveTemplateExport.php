<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class YtmNormalCurveTemplateExport implements FromArray, WithHeadings, WithStyles
{
    public function headings(): array
    {
        return ['rating_kode', 'tenor_bulan', 'ytm_normal', 'data_date', 'rating_aaa', 'rating_aa', 'rating_a', 'rating_bbb', 'source'];
    }

    public function array(): array
    {
        return [
            ['AAA', 12, 5.5],
            ['AAA', 36, 6.0],
            ['AAA', 60, 6.3],
            ['AA+', 12, 5.8],
            ['AA+', 36, 6.3],
            ['AA+', 60, 6.6],
            ['', 12, '', now()->toDateString(), 0.50, 0.80, 1.25, 1.75, 'PHEI'],
            ['', 36, '', now()->toDateString(), 0.75, 1.05, 1.55, 2.10, 'PHEI'],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
