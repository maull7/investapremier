<?php

namespace App\Imports\Sheets;

use App\Models\AnalisaReksaDana;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

class AnalisaEfekSheet implements ToCollection, WithHeadingRow
{
    public function __construct(private AnalisaReksaDana $analisa) {}

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            if (empty($row['kode_efek'])) continue;
            $this->analisa->efek()->create([
                'kode_efek'          => $row['kode_efek'],
                'nama_efek'          => $row['nama_efek'] ?? '',
                'sektor'             => $row['sektor'] ?? null,
                'bobot'              => $row['bobot'] ?? 0,
                'nilai_pasar'        => $row['nilai_pasar'] ?? null,
                'kontribusi_kinerja' => $row['kontribusi_kinerja'] ?? null,
                'return_1m'          => $row['return_1m'] ?? null,
                'return_3m'          => $row['return_3m'] ?? null,
                'return_6m'          => $row['return_6m'] ?? null,
                'return_1y'          => $row['return_1y'] ?? null,
                'market_cap'         => $row['market_cap'] ?? null,
                'top_10'             => !empty($row['top_10']) && strtolower((string)$row['top_10']) === 'ya',
            ]);
        }
    }
}
