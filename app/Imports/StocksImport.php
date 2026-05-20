<?php

namespace App\Imports;

use App\Models\Stock;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class StocksImport implements ToCollection, WithHeadingRow, WithCalculatedFormulas
{
    public int $imported = 0;

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            if (empty(trim($row['kode'] ?? ''))) continue;

            $toNum = function ($val) {
                if ($val === null || $val === '') return null;
                if (is_numeric($val)) return $val;
                $s = str_replace(['.', ','], ['', '.'], (string) $val);
                if (!is_numeric($s)) return null;
                return $s;
            };

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

            Stock::updateOrCreate(
                ['kode' => strtoupper(trim($row['kode']))],
                [
                    'nama' => $row['nama'] ?? null,
                    'sektor' => $row['sektor'] ?? null,
                    'sub_industri' => $row['sub_industri'] ?? null,
                    'harga_terbaru' => $toNum($row['harga_terbaru'] ?? null),
                    'harga_penutupan_sebelumnya' => $toNum($row['harga_penutupan_sebelumnya'] ?? null),
                    'harga_pembukaan' => $toNum($row['harga_pembukaan'] ?? null),
                    'harga_tertinggi' => $toNum($row['harga_tertinggi'] ?? null),
                    'harga_terendah' => $toNum($row['harga_terendah'] ?? null),
                    'volume' => $toNum($row['volume'] ?? null),
                    'value' => $toNum($row['value'] ?? null),
                    'frekuensi' => $toNum($row['frekuensi'] ?? null),
                    'jumlah_saham' => $toNum($row['jumlah_saham'] ?? $row['volume_2'] ?? null),
                    'market_capital' => $toNum($row['market_capital'] ?? null),
                    'last_update' => $lastUpdate,
                ]
            );

            $this->imported++;
        }
    }
}
