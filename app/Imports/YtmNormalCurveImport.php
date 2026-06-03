<?php

namespace App\Imports;

use App\Models\RatingObligasi;
use App\Models\YtmNormalCurve;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Illuminate\Support\Collection;

class YtmNormalCurveImport implements ToCollection, WithHeadingRow, WithCalculatedFormulas
{
    public int $imported = 0;
    public array $errors = [];

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            $ratingKode = strtoupper(trim($row['rating_kode'] ?? ''));
            $tenorBulan = (int) ($row['tenor_bulan'] ?? 0);
            $ytmNormal = $row['ytm_normal'] ?? null;

            if (empty($ratingKode) || $tenorBulan < 1 || !is_numeric($ytmNormal)) {
                continue;
            }

            $rating = RatingObligasi::where('kode', $ratingKode)->first();
            if (!$rating) {
                $this->errors[] = "Rating kode '{$ratingKode}' tidak ditemukan. (tenor: {$tenorBulan})";
                continue;
            }

            YtmNormalCurve::updateOrCreate(
                ['rating_id' => $rating->id, 'tenor_bulan' => $tenorBulan],
                ['ytm_normal' => (float) $ytmNormal]
            );

            $this->imported++;
        }
    }
}
