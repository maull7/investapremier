<?php

namespace App\Exports;

use App\Exports\Sheets\SektorTemplateSheet;
use App\Exports\Sheets\EfekTemplateSheet;
use App\Exports\Sheets\KinerjaTemplateSheet;
use App\Exports\Sheets\ObligasiTemplateSheet;
use App\Exports\Sheets\BankTemplateSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AnalisaTemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new SektorTemplateSheet(),
            new EfekTemplateSheet(),
            new KinerjaTemplateSheet(),
            new ObligasiTemplateSheet(),
            new BankTemplateSheet(),
        ];
    }
}
