<?php

namespace App\Imports;

use App\Models\AnalisaReksaDana;
use App\Imports\Sheets\AnalisaSektorSheet;
use App\Imports\Sheets\AnalisaEfekSheet;
use App\Imports\Sheets\AnalisaKinerjaSheet;
use App\Imports\Sheets\AnalisaObligasiSheet;
use App\Imports\Sheets\AnalisaBankSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AnalisaImport implements WithMultipleSheets
{
    public function __construct(private AnalisaReksaDana $analisa) {}

    public function sheets(): array
    {
        return [
            'Sektor'   => new AnalisaSektorSheet($this->analisa),
            'Efek'     => new AnalisaEfekSheet($this->analisa),
            'Kinerja'  => new AnalisaKinerjaSheet($this->analisa),
            'Obligasi' => new AnalisaObligasiSheet($this->analisa),
            'Bank'     => new AnalisaBankSheet($this->analisa),
        ];
    }
}
