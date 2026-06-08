<?php

namespace App\Imports;

use App\Models\RatingObligasi;
use App\Models\PheiCreditSpreadMatrix;
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
            if ($this->isCreditSpreadMatrixRow($row)) {
                $this->importCreditSpreadMatrixRow($row);
                continue;
            }

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

    private function isCreditSpreadMatrixRow($row): bool
    {
        return !empty($row['data_date'])
            && !empty($row['tenor_bulan'])
            && (
                is_numeric($row['rating_aaa'] ?? null)
                || is_numeric($row['rating_aa'] ?? null)
                || is_numeric($row['rating_a'] ?? null)
                || is_numeric($row['rating_bbb'] ?? null)
            );
    }

    private function importCreditSpreadMatrixRow($row): void
    {
        try {
            $rawDate = $row['data_date'] ?? null;
            $date = is_numeric($rawDate)
                ? \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($rawDate)->format('Y-m-d')
                : \Illuminate\Support\Carbon::parse($rawDate)->toDateString();
        } catch (\Throwable) {
            $this->errors[] = 'Tanggal data Credit Spread Matrix tidak valid.';
            return;
        }

        PheiCreditSpreadMatrix::updateOrCreate(
            [
                'data_date' => $date,
                'tenor_bulan' => (int) $row['tenor_bulan'],
                'source' => $row['source'] ?? 'PHEI',
            ],
            [
                'rating_aaa' => is_numeric($row['rating_aaa'] ?? null) ? (float) $row['rating_aaa'] : null,
                'rating_aa' => is_numeric($row['rating_aa'] ?? null) ? (float) $row['rating_aa'] : null,
                'rating_a' => is_numeric($row['rating_a'] ?? null) ? (float) $row['rating_a'] : null,
                'rating_bbb' => is_numeric($row['rating_bbb'] ?? null) ? (float) $row['rating_bbb'] : null,
            ]
        );

        $this->imported++;
    }
}
