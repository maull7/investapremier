<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class HargaUnitLinkTemplateExport implements FromArray, WithHeadings, WithTitle
{
    public function array(): array
    {
        return [
            ['AIA IDR Balanced Syariah Fund', '2010-06-21 00:00:00', 1000.00, null, null],
        ];
    }

    public function headings(): array
    {
        return [
            'Nama Unit Link',
            'DateTime',
            'Harga Median',
            'Sell-Buy (low)',
            'Sell-Buy (high)',
        ];
    }

    public function title(): string
    {
        return 'Harga Unit Link';
    }
}
