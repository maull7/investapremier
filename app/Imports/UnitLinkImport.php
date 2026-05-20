<?php

namespace App\Imports;

use App\Models\UnitLink;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class UnitLinkImport implements ToCollection, WithHeadingRow, WithCalculatedFormulas
{
    public int $imported = 0;

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            $name = trim($row['unit_link'] ?? '');
            if (empty($name)) continue;

            $lastUpdate = null;
            if (!empty($row['last_update'])) {
                $v = $row['last_update'];
                if (is_numeric($v)) {
                    try {
                        $lastUpdate = Date::excelToDateTimeObject((float) $v)->format('Y-m-d');
                    } catch (\Throwable $e) {
                        $lastUpdate = null;
                    }
                } else {
                    $lastUpdate = date('Y-m-d', strtotime((string) $v)) ?: null;
                }
            }

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
