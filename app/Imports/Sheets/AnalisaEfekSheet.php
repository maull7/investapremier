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
                'kontribusi_kinerja' => $row['kontribusi_kinerja'] ?? null,
                'market_cap'         => $row['market_cap'] ?? null,
                'top_10'             => !empty($row['top_10']) && strtolower((string)$row['top_10']) === 'ya',
            ]);
        }
    }
}
