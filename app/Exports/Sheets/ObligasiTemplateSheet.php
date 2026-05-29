<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ObligasiTemplateSheet implements FromArray, WithHeadings, WithTitle, WithStyles
{
    public function title(): string { return 'Obligasi'; }

    public function headings(): array
    {
        return [
            'kode_obligasi', 'nama_obligasi', 'bobot', 'nilai_pasar',
            'return_1m', 'return_3m', 'return_6m', 'return_1y',
            'durasi', 'rating',
        ];
    }

    public function array(): array
    {
        return [
            ['FR0091', 'Obligasi Negara FR0091', 15.00, '', '', '', '', '', 7.50, 'AAA'],
            ['BBRI01', 'Obligasi BRI 2025', 8.00, '', '', '', '', '', 3.20, 'AA+'],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
