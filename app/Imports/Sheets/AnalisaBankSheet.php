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
                'bobot'              => $row['bobot'] ?? 0,
                'car'                => $row['car'] ?? null,
                'npl'                => $row['npl'] ?? null,
                'klasifikasi_risiko' => $row['klasifikasi_risiko'] ?? null,
            ]);
        }
    }
}
