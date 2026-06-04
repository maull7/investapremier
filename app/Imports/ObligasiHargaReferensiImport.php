<?php

namespace App\Imports;

use App\Models\ObligasiHargaReferensi;
use App\Support\ExcelDateHelper;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Illuminate\Support\Collection;

class ObligasiHargaReferensiImport implements ToCollection, WithHeadingRow, WithCalculatedFormulas
{
    use ExcelDateHelper;
    public int $imported = 0;

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            if (empty(trim($row['kode'] ?? ''))) continue;

            $toNum = fn($v) => is_numeric($v) ? $v : (is_numeric(str_replace(',', '', $v)) ? str_replace(',', '', $v) : null);

            ObligasiHargaReferensi::updateOrCreate(
                ['kode' => strtoupper(trim($row['kode']))],
                [
                    'nama' => $row['nama'] ?? null,
                    'tanggal_terbit' => $this->parseExcelDate($row['tanggal_terbit'] ?? null),
                    'emiten' => $row['emiten'] ?? null,
                    'sektor' => $row['sektor'] ?? null,
                    'sub_sektor' => $row['sub_sektor'] ?? null,
                    'industri' => $row['industri'] ?? null,
                    'sub_industri' => $row['sub_industri'] ?? null,
                    'denominasi' => $row['denominasi'] ?? null,
                    'rating' => $row['rating'] ?? null,
                    'syariah' => isset($row['syariah']) ? in_array(strtolower(trim($row['syariah'])), ['ya', 'yes', '1', 'true']) : null,
                    'kupon' => $toNum($row['kupon'] ?? null),
                    'jatuh_tempo' => $this->parseExcelDate($row['jatuh_tempo'] ?? null),
                    'harga_persen' => $toNum($row['harga_persen'] ?? $row['harga_(%)'] ?? null),
                    'ttm' => $toNum($row['ttm'] ?? null),
                    'ytm' => $toNum($row['ytm'] ?? null),
                    'current_yield' => $toNum($row['current_yield'] ?? null),
                    'total_val' => $toNum($row['total_val'] ?? null),
                    'outstanding_amount' => $toNum($row['outstanding_amount'] ?? null),
                ]
            );

            $this->imported++;
        }
    }
}
