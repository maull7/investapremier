<?php

namespace App\Imports\Sheets;

use App\Models\AnalisaReksaDana;
use App\Support\ExcelDateHelper;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

class AnalisaKinerjaSheet implements ToCollection, WithHeadingRow
{
    use ExcelDateHelper;
    public function __construct(private AnalisaReksaDana $analisa) {}

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            if (empty($row['periode'])) continue;
            $this->analisa->kinerja()->create([
                'periode'    => $this->parseExcelDate($row['periode']),
                'return_pct' => $row['return_pct'] ?? 0,
            ]);
        }
    }
}
