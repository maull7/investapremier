<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SektorTemplateSheet implements FromArray, WithHeadings, WithTitle, WithStyles
{
    public function title(): string { return 'Sektor'; }

    public function headings(): array
    {
        return ['nama_sektor', 'bobot'];
    }

    public function array(): array
    {
        return [['Keuangan', 25.50], ['Energi', 15.00]];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
