<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class HarianReksaDanaTemplateExport implements FromArray, WithHeadings, WithTitle
{
    public function title(): string
    {
        return 'Harian Reksa Dana';
    }

    public function headings(): array
    {
        return ['nama_reksa_dana', 'tanggal', 'nab_per_unit'];
    }

    public function array(): array
    {
        return [['Reksa Dana Contoh', '2026-05-18', '1500.123456']];
    }
}
