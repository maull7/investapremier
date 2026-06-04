<?php

namespace App\Imports;

use App\Models\UnitLink;
use App\Support\ExcelDateHelper;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Illuminate\Support\Collection;

class UnitLinkImport implements ToCollection, WithHeadingRow, WithCalculatedFormulas
{
    use ExcelDateHelper;
    public int $imported = 0;

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            $name = trim($row['unit_link'] ?? '');
            if (empty($name)) continue;

            $lastUpdate = $this->parseExcelDate($row['last_update'] ?? null);

            UnitLink::updateOrCreate(
                ['unit_link' => $name],
                [
                    'asuransi' => $row['asuransi'] ?? null,
                    'jenis' => $row['jenis'] ?? null,
                    'tipe' => $row['tipe'] ?? null,
                    'mata_uang' => $row['mata_uang'] ?? null,
                    'median_price' => $this->toNum($row['median_price'] ?? null),
                    'buy_price' => $this->toNum($row['buy_price'] ?? null),
                    'sell_price' => $this->toNum($row['sell_price'] ?? null),
                    'last_update' => $lastUpdate,
                ]
            );

            $this->imported++;
        }
    }

    private function toNum($val)
    {
        if ($val === null || $val === '') return null;
        if (is_numeric($val)) return $val;
        $s = str_replace(['.', ','], ['', '.'], (string) $val);
        if (!is_numeric($s)) return null;
        return $s;
    }
}
