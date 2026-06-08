<?php

namespace App\Exports;

use App\Support\ExcelDateHelper;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SekuritasInformasiTemplateExport implements FromArray, WithHeadings, WithStyles, WithColumnFormatting
{
    use ExcelDateHelper;

    public function headings(): array
    {
        return [
            'kode_obligasi',
            'nama_obligasi',
            'isin_code',
            'currency',
            'outstanding_amount',
            'coupon',
            'maturity_date',
        ];
    }

    public function array(): array
    {
        return [
            [
                'FR0037',
                'Obligasi Negara RI Seri FR0037',
                'IDG000006800',
                'IDR',
                2417000000000,
                12.000,
                $this->excelDateValue('2026-09-15'),
            ],
            [
                'FR0040',
                'Obligasi Negara RI Seri FR0040',
                'IDG000007000',
                'IDR',
                15000000000000,
                8.375,
                $this->excelDateValue('2034-04-15'),
            ],
        ];
    }

    public function columnFormats(): array
    {
        return $this->dateColumnFormats(['G']);
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
