<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class HargaReksaDanaTemplateExport implements FromArray, WithHeadings, WithTitle
{
    public function title(): string
    {
        return 'Harga Reksa Dana';
    }

    public function headings(): array
    {
        return [
            'nama_reksa_dana',
            'nama_manajer_investasi',
            'jenis',
            'kategori',
            'mata_uang',
            'nab_per_unit',
            'tanggal_nab',
        ];
    }

    public function array(): array
    {
        return [
            [
                'Reksa Dana Contoh',
                'PT Manajer Investasi',
                'Saham',
                'Ekuitas, Pertumbuhan',
                'IDR',
                '1500.123456',
                '2026-05-18',
            ],
        ];
    }
}
