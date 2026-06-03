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
        return ['rating_kode', 'tenor_bulan', 'ytm_normal'];
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
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
