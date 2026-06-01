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

        $periods = [];
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
            $kodeOjk = trim($row['kode_ojk'] ?? $row['ojk'] ?? '');

            $manager = InvestmentManager::firstOrCreate(
                ['name' => $name],
                [
                    'kode_mi' => $kodeMi ?: null,
                    'kode_ojk' => $kodeOjk ?: null,
                ]
            );

            if ($kodeMi && !$manager->kode_mi) {
                $manager->update(['kode_mi' => $kodeMi]);
            }
            if ($kodeOjk && !$manager->kode_ojk) {
                $manager->update(['kode_ojk' => $kodeOjk]);
            }

            foreach ($periods as $date => $keys) {
                $aum = $this->parseIdr($row[$keys['aum']] ?? null);
                $up = $this->parseIdr($row[$keys['up']] ?? null);

                if ($aum !== null || $up !== null) {
                    $data = [
                        'aum' => $aum,
                        'up' => $up,
                    ];

                    $mataUang = trim($row['mata_uang'] ?? '');
                    if ($mataUang) {
                        $data['mata_uang'] = strtoupper($mataUang);
                    }

                    $tahun = trim($row['tahun'] ?? '');
                    if ($tahun !== '' && is_numeric($tahun)) {
                        $data['tahun'] = (int) $tahun;
                    }

                    $kuartal = trim($row['kuartal'] ?? '');
                    if ($kuartal !== '' && is_numeric($kuartal)) {
                        $data['kuartal'] = (int) $kuartal;
                    }

                    InvestmentManagerPeriod::updateOrCreate(
                        [
                            'investment_manager_id' => $manager->id,
                            'period_date' => $date,
                        ],
                        $data
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
