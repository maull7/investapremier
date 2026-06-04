<?php

namespace App\Exports;

use App\Support\ExcelDateHelper;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

class HargaUnitLinkTemplateExport implements FromArray, WithHeadings, WithTitle, WithColumnFormatting
{
    use ExcelDateHelper;

    public function title(): string { return 'Harga Unit Link'; }

    public function headings(): array
    {
        return ['Nama Unit Link', 'DateTime', 'Harga Median', 'Sell-Buy (low)', 'Sell-Buy (high)'];
    }

    public function array(): array
    {
        return [
            [
                'AIA IDR Balanced Syariah Fund',
                $this->excelDateValue('2010-06-21'),   // B – DateTime
                1000.00, null, null,
            ],
        ];
    }

    /** Apply DATE format to DateTime (col B). */
    public function columnFormats(): array
    {
        return $this->dateColumnFormats(['B']);
    }
}
