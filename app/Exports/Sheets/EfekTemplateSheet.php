<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EfekTemplateSheet implements FromArray, WithHeadings, WithTitle, WithStyles
{
    public function title(): string { return 'Efek'; }

    public function headings(): array
    {
        return ['kode_efek', 'nama_efek', 'sektor', 'bobot', 'kontribusi_kinerja', 'market_cap', 'top_10'];
    }

    public function array(): array
    {
        return [
            ['BBCA', 'Bank Central Asia Tbk', 'Keuangan', 10.50, 0.35, 950000000000000, 'Ya'],
            ['TLKM', 'Telkom Indonesia Tbk', 'Teknologi', 8.20, -0.12, 280000000000000, 'Ya'],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
