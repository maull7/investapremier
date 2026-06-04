<?php

namespace App\Exports;

use App\Support\ExcelDateHelper;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

class HargaReksaDanaTemplateExport implements FromArray, WithHeadings, WithTitle, WithColumnFormatting
{
    use ExcelDateHelper;

    public function title(): string { return 'Harga Reksa Dana'; }

    public function headings(): array
    {
        return [
            'kode_reksa_dana', 'nama_reksa_dana', 'nama_manajer_investasi',
            'jenis', 'kategori', 'kategori_produk', 'mata_uang',
            'nab_per_unit', 'tanggal_nab',
        ];
    }

    public function array(): array
    {
        return [
            [
                'GR003D001', 'Reksa Dana Contoh', 'PT Manajer Investasi',
                'Saham', 'Ekuitas, Pertumbuhan', 'Konvensional', 'IDR',
                1500.123456, $this->excelDateValue('2026-05-18'),
            ],
            [
                'GR003D1001', 'Reksa Dana Syariah Contoh', 'PT Manajer Investasi',
                'Saham', 'Ekuitas, Syariah', 'Syariah', 'IDR',
                2000.500000, $this->excelDateValue('2026-05-18'),
            ],
        ];
    }

    /** Apply DATE format to tanggal_nab (col I). */
    public function columnFormats(): array
    {
        return $this->dateColumnFormats(['I']);
    }
}
