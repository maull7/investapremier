<?php

namespace App\Imports\Sheets;

use App\Models\AnalisaReksaDana;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

class AnalisaSektorSheet implements ToCollection, WithHeadingRow
{
    public function __construct(private AnalisaReksaDana $analisa) {}

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            if (empty($row['nama_sektor'])) continue;
            $this->analisa->sektor()->create([
                'nama_sektor' => $row['nama_sektor'],
                'bobot'       => $row['bobot'] ?? 0,
            ]);
        }
    }
}
