<?php

namespace App\Exports;

use App\Support\ExcelDateHelper;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class HarianReksaDanaTemplateExport implements FromArray, WithHeadings, WithTitle, WithColumnFormatting, WithStyles
{
    use ExcelDateHelper;

    public function title(): string
    {
        return 'Harian Reksa Dana';
    }

    public function headings(): array
    {
        return ['nama_reksa_dana', 'tanggal', 'nab_per_unit'];
    }

    public function array(): array
    {
        return [
            ['Reksa Dana Contoh', $this->excelDateValue('2026-05-18'), '1500.123456'],
        ];
    }

    public function columnFormats(): array
    {
        return $this->dateColumnFormats(['B']);
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
