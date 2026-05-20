<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StocksTemplateExport implements FromArray, WithHeadings, WithStyles
{
    public function headings(): array
    {
        return [
            'kode', 'nama', 'sektor', 'sub_industri',
            'harga_terbaru', 'harga_penutupan_sebelumnya',
            'harga_pembukaan', 'harga_tertinggi', 'harga_terendah',
            'volume', 'value', 'frekuensi', 'jumlah_saham',
            'market_capital', 'last_update',
        ];
    }

    public function array(): array
    {
        return [
            [
                'AADI', 'Adaro Andalan Indonesia Tbk.', 'Energi', 'Produksi Batu Bara',
                8200, 8950, 8950, 9100, 7825,
                67259900, 554546260000, 21888, 7786891760,
                63852512432000, '2026-05-19',
            ],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
