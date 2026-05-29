<?php

namespace App\Imports\Sheets;

use App\Models\AnalisaReksaDana;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

class AnalisaSukukSheet implements ToCollection, WithHeadingRow
{
    public function __construct(private AnalisaReksaDana $analisa) {}

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            if (empty($row['kode_sukuk'])) continue;
            $this->analisa->sukuk()->create([
                'kode_sukuk'  => $row['kode_sukuk'],
                'nama_sukuk'  => $row['nama_sukuk'] ?? '',
                'jenis_sukuk' => $row['jenis_sukuk'] ?? null,
                'bobot'       => $row['bobot'] ?? 0,
                'yield'       => $row['yield'] ?? null,
                'jatuh_tempo' => $row['jatuh_tempo'] ?? null,
                'rating'      => $row['rating'] ?? null,
            ]);
        }
    }
}
