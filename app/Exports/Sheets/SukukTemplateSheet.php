<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SukukTemplateSheet implements FromArray, WithHeadings, WithTitle, WithStyles
{
    public function title(): string { return 'Sukuk'; }

    public function headings(): array
    {
        return [
            'kode_sukuk', 'nama_sukuk', 'jenis_sukuk', 'bobot',
            'yield', 'jatuh_tempo', 'rating',
        ];
    }

    public function array(): array
    {
        return [
            ['SR019', 'Sukuk Ritel SR019', 'Negara', 15.00, 6.25, '2028', 'AAA'],
            ['ISAT01', 'Sukuk Indosat', 'Korporasi', 5.00, 7.10, '2029', 'AA+'],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
