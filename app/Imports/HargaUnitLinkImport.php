<?php

namespace App\Imports;

use App\Models\UnitLink;
use App\Models\HargaUnitLink;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class HargaUnitLinkImport implements ToModel, WithHeadingRow, SkipsEmptyRows
{
    public int $imported = 0;

    public function model(array $row): ?HargaUnitLink
    {
        $name = trim($row['nama_unit_link'] ?? '');
        if (empty($name) || empty($row['datetime'] ?? '')) return null;

        $unitLink = UnitLink::where('unit_link', $name)->first();
        if (!$unitLink) return null;

        try {
            $datetime = \Carbon\Carbon::parse($row['datetime']);
        } catch (\Exception $e) {
            return null;
        }

        $this->imported++;

        return HargaUnitLink::updateOrCreate(
            ['unit_link_id' => $unitLink->id, 'datetime' => $datetime],
            [
                'harga_median'  => $this->parseNumeric($row['harga_median'] ?? 0),
                'sell_buy_low'  => $this->parseNumeric($row['sell_buy_low'] ?? null),
                'sell_buy_high' => $this->parseNumeric($row['sell_buy_high'] ?? null),
            ]
        );
    }

    private function parseNumeric(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        $clean = preg_replace('/\.(?=\d{3})/', '', (string) $value);
        $clean = str_replace(',', '.', $clean);

        return is_numeric($clean) ? (float) $clean : null;
    }
}
