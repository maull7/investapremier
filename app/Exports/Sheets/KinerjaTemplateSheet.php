<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class KinerjaTemplateSheet implements FromArray, WithHeadings, WithTitle, WithStyles
{
    public function title(): string { return 'Kinerja'; }

    public function headings(): array
    {
        return ['periode', 'return_pct'];
    }

    public function array(): array
    {
        return [
            ['2024-01-01', 1.25],
            ['2024-02-01', -0.50],
            ['2024-03-01', 2.10],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
