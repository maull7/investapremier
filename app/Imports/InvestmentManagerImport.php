<?php

namespace App\Imports;

use App\Models\InvestmentManager;
use App\Models\InvestmentManagerPeriod;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Illuminate\Support\Collection;

class InvestmentManagerImport implements ToCollection, WithHeadingRow, WithCalculatedFormulas
{
    public int $imported = 0;

    public function collection(Collection $rows): void
    {
        if ($rows->isEmpty()) return;

        $headings = array_keys($rows->first()->toArray());

        $periods = []; // [date => [aumKey, upKey, originalHeadingAUM, originalHeadingUP]]
        foreach ($headings as $h) {
            if (preg_match('/^(aum|up)[\s_]+(.+)$/i', $h, $m)) {
                $type = strtolower($m[1]);
                $dateStr = trim(str_replace('_', ' ', $m[2]));
                $date = date('Y-m-d', strtotime($dateStr));
                if ($date) {
                    $periods[$date][$type] = $h;
                }
            }
        }

        foreach ($rows as $row) {
            $name = trim($row['nama_investment_manager'] ?? $row['name'] ?? $row['nama'] ?? '');
            if (empty($name)) continue;

            $kodeMi = trim($row['kode_mi'] ?? $row['kode'] ?? '');

            $manager = InvestmentManager::firstOrCreate(
                ['name' => $name],
                ['kode_mi' => $kodeMi ?: null]
            );

            if ($kodeMi && !$manager->kode_mi) {
                $manager->update(['kode_mi' => $kodeMi]);
            }

            foreach ($periods as $date => $keys) {
                $aum = $this->parseIdr($row[$keys['aum']] ?? null);
                $up = $this->parseIdr($row[$keys['up']] ?? null);

                if ($aum !== null || $up !== null) {
                    InvestmentManagerPeriod::updateOrCreate(
                        [
                            'investment_manager_id' => $manager->id,
                            'period_date' => $date,
                        ],
                        [
                            'aum' => $aum,
                            'up' => $up,
                        ]
                    );
                }
            }

            $this->imported++;
        }
    }

    private function parseIdr($val)
    {
        if ($val === null || $val === '' || $val === '-') return null;
        if (is_numeric($val)) return $val;
        $s = (string) $val;
        $s = str_replace('Rp ', '', $s);
        $s = str_replace('.', '', $s);
        $s = str_replace(',', '.', $s);
        return is_numeric($s) ? $s : null;
    }
}
