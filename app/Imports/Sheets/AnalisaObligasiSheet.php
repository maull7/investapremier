<?php

namespace App\Imports\Sheets;

use App\Models\AnalisaReksaDana;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

class AnalisaObligasiSheet implements ToCollection, WithHeadingRow
{
    public function __construct(private AnalisaReksaDana $analisa) {}

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            if (empty($row['kode_obligasi'])) continue;
            $this->analisa->obligasi()->create([
                'kode_obligasi' => $row['kode_obligasi'],
                'nama_obligasi' => $row['nama_obligasi'] ?? '',
                'bobot'         => $row['bobot'] ?? 0,
                'nilai_pasar'   => $row['nilai_pasar'] ?? null,
                'return_1m'     => $row['return_1m'] ?? null,
                'return_3m'     => $row['return_3m'] ?? null,
                'return_6m'     => $row['return_6m'] ?? null,
                'return_1y'     => $row['return_1y'] ?? null,
                'durasi'        => $row['durasi'] ?? null,
                'rating'        => $row['rating'] ?? null,
            ]);
        }
    }
}
