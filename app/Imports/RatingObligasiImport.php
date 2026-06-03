<?php

namespace App\Imports;

use App\Models\RatingObligasi;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Illuminate\Support\Collection;

class RatingObligasiImport implements ToCollection, WithHeadingRow, WithCalculatedFormulas
{
    public int $imported = 0;

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            $kode = trim($row['kode'] ?? '');
            if (empty($kode)) continue;

            RatingObligasi::updateOrCreate(
                ['kode' => strtoupper($kode)],
                [
                    'nama' => $row['nama'] ?? null,
                    'keterangan' => $row['keterangan'] ?? null,
                    'urutan' => is_numeric($row['urutan'] ?? false) ? (int) $row['urutan'] : 0,
                ]
            );

            $this->imported++;
        }
    }
}
