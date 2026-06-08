<?php

namespace App\Imports;

use App\Models\SekuritasInformasi;
use App\Support\ExcelDateHelper;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Illuminate\Support\Collection;

class SekuritasInformasiImport implements ToCollection, WithHeadingRow, WithCalculatedFormulas
{
    use ExcelDateHelper;

    public int $imported = 0;
    public string $type;

    public function __construct(string $type)
    {
        $this->type = $type;
    }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            if (empty(trim($row['kode_obligasi'] ?? ''))) {
                continue;
            }

            $toNum = function ($val) {
                if ($val === null || $val === '') {
                    return null;
                }
                if (is_numeric($val)) {
                    return $val;
                }
                $s = str_replace(['.', ','], ['', '.'], (string) $val);
                if (!is_numeric($s)) {
                    return null;
                }
                return $s;
            };

            $maturityDate = $this->parseExcelDate($row['maturity_date'] ?? null);

            SekuritasInformasi::updateOrCreate(
                [
                    'type' => $this->type,
                    'kode_obligasi' => strtoupper(trim($row['kode_obligasi'])),
                ],
                [
                    'nama_obligasi' => $row['nama_obligasi'] ?? null,
                    'isin_code' => $row['isin_code'] ?? null,
                    'currency' => $row['currency'] ?? 'IDR',
                    'outstanding_amount' => $toNum($row['outstanding_amount'] ?? null),
                    'coupon' => $toNum($row['coupon'] ?? null),
                    'maturity_date' => $maturityDate,
                ]
            );

            $this->imported++;
        }
    }
}
