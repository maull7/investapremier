<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BankTemplateSheet implements FromArray, WithHeadings, WithTitle, WithStyles
{
    public function title(): string { return 'Bank'; }

    public function headings(): array
    {
        return ['nama_bank', 'bobot', 'car', 'npl', 'klasifikasi_risiko'];
    }

    public function array(): array
    {
        return [
            ['Bank BCA', 20.00, 25.50, 1.20, 'Rendah'],
            ['Bank Mandiri', 15.00, 21.30, 2.10, 'Rendah'],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
