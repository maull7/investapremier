<?php

namespace App\Imports;

use App\Imports\Sheets\PreviewSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AnalisaImportPreview implements WithMultipleSheets
{
    private array $sheets = [];

    public function __construct()
    {
        foreach (['Sektor', 'Efek', 'Kinerja', 'Obligasi', 'Sukuk', 'Bank'] as $name) {
            $this->sheets[$name] = new PreviewSheet;
        }
    }

    public function sheets(): array
    {
        return $this->sheets;
    }

    public function getData(): array
    {
        $data = [];
        foreach ($this->sheets as $name => $sheet) {
            $data[strtolower($name)] = $sheet->rows;
        }
        return $data;
    }
}
