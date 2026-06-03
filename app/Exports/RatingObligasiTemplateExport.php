<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RatingObligasiTemplateExport implements FromArray, WithHeadings, WithStyles
{
    public function headings(): array
    {
        return ['kode', 'nama', 'keterangan', 'urutan'];
    }

    public function array(): array
    {
        return [
            ['AAA', 'AAA (Triple A)', 'Peringkat tertinggi. Kemampuan sangat kuat memenuhi kewajiban keuangan.', 1],
            ['AA+', 'AA+ (Double A Plus)', 'Peringkat sangat tinggi, sedikit lebih rendah dari AAA.', 2],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
