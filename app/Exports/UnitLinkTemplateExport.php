<?php

namespace App\Exports;

use App\Support\ExcelDateHelper;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UnitLinkTemplateExport implements FromArray, WithHeadings, WithStyles, WithColumnFormatting
{
    use ExcelDateHelper;

    public function headings(): array
    {
        return [
            'Unit Link', 'Asuransi', 'Jenis', 'Tipe', 'Mata Uang',
            'Median Price', 'Buy Price', 'Sell Price', 'Last Update',
        ];
    }

    public function array(): array
    {
        return [
            [
                'AFI Dynamic Money Rp', 'AFI', 'Saham', 'Konvensional', 'IDR',
                1145.6496, 1117.7069, 1173.5922,
                $this->excelDateValue('2026-05-18'),   // I – Last Update
            ],
        ];
    }

    /** Apply DATE format to Last Update (col I). */
    public function columnFormats(): array
    {
        return $this->dateColumnFormats(['I']);
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
