<?php

namespace App\Exports\Sheets;

use App\Support\ExcelDateHelper;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class KinerjaTemplateSheet implements FromArray, WithHeadings, WithTitle, WithStyles, WithColumnFormatting
{
    use ExcelDateHelper;

    public function title(): string { return 'Kinerja'; }

    public function headings(): array
    {
        return ['periode', 'return_pct'];
    }

    public function array(): array
    {
        return [
            [$this->excelDateValue('2024-01-01'),  1.25],
            [$this->excelDateValue('2024-02-01'), -0.50],
            [$this->excelDateValue('2024-03-01'),  2.10],
        ];
    }

    /** Apply DATE format to periode (col A). */
    public function columnFormats(): array
    {
        return $this->dateColumnFormats(['A']);
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
