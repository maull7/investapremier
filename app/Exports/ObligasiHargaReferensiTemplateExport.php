<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ObligasiHargaReferensiTemplateExport implements FromArray, WithHeadings, WithStyles
{
    public function headings(): array
    {
        return [
            'kode', 'nama', 'tanggal_terbit', 'emiten', 'sektor', 'sub_sektor',
            'industri', 'sub_industri', 'denominasi', 'rating', 'syariah',
            'kupon', 'jatuh_tempo', 'harga_persen', 'ttm', 'ytm',
            'current_yield', 'total_val', 'outstanding_amount',
        ];
    }

    public function array(): array
    {
        return [
            [
                'ABLS01XXMF', 'MTN Asian Bulk Logistics I Tahun 2022', '2022-06-21', 'ABLS',
                '', '', '', '', 'IDR', '', 'Tidak',
                0.09, '2027-06-21', 100, 1.08966, 0.09,
                0.09, 100000000, 1000000000000,
            ],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
