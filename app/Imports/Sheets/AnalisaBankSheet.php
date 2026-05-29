<?php

namespace App\Imports\Sheets;

use App\Models\AnalisaReksaDana;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

class AnalisaBankSheet implements ToCollection, WithHeadingRow
{
    public function __construct(private AnalisaReksaDana $analisa) {}

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            if (empty($row['nama_bank'])) continue;
            $this->analisa->bank()->create([
                'nama_bank'          => $row['nama_bank'],
                'jenis_bank'         => $row['jenis_bank'] ?? null,
                'bobot'              => $row['bobot'] ?? 0,
                'nilai_pasar'        => $row['nilai_pasar'] ?? null,
                'return_1m'          => $row['return_1m'] ?? null,
                'return_3m'          => $row['return_3m'] ?? null,
                'return_6m'          => $row['return_6m'] ?? null,
                'return_1y'          => $row['return_1y'] ?? null,
                'car'                => $row['car'] ?? null,
                'npl'                => $row['npl'] ?? null,
                'klasifikasi_risiko' => $row['klasifikasi_risiko'] ?? null,
            ]);
        }
    }
}
