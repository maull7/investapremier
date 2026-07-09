<?php

namespace App\Imports;

use App\Models\HargaReksaDana;
use App\Models\ReksaDana;
use App\Support\ExcelDateHelper;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class HargaReksaDanaImport implements ToCollection, WithHeadingRow
{
    use ExcelDateHelper;

    public int $imported = 0;
    public int $skipped = 0;

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            $row = $row->mapWithKeys(fn ($v, $k) => [strtolower(trim((string) $k)) => $v])->all();

            $reksaDana = $this->resolveReksaDana($row);
            if (!$reksaDana) {
                $this->skipped++;
                continue;
            }

            $tanggal = $this->parseDate($row);
            $nab = $this->parseNab($row);

            if (!$tanggal || $nab === null) {
                $this->skipped++;
                continue;
            }

            if (!$reksaDana->tanggal_nab || $tanggal >= $reksaDana->tanggal_nab->toDateString()) {
                $reksaDana->update([
                    'nab_per_unit' => $nab,
                    'tanggal_nab' => $tanggal,
                ]);
            }

            HargaReksaDana::updateOrCreate(
                ['reksa_dana_id' => $reksaDana->id, 'tanggal' => $tanggal],
                ['nab_per_unit' => $nab]
            );

            $this->imported++;
        }
    }

    protected function resolveReksaDana(array $row): ?ReksaDana
    {
        $kode = $this->pick($row, ['kode_reksa_dana', 'kode', 'code']);
        if ($kode) {
            $rd = ReksaDana::where('kode_reksa_dana', trim((string) $kode))->first();
            if ($rd) return $rd;
        }

        $nama = $this->pick($row, ['nama_reksa_dana', 'nama_rd', 'fund_name', 'nama', 'reksadana']);
        if ($nama) {
            return ReksaDana::where('nama_reksa_dana', trim((string) $nama))->first();
        }

        return null;
    }

    protected function parseDate(array $row): ?string
    {
        $raw = $this->pick($row, ['tanggal_nab', 'tanggal', 'date', 'tgl', 'tgl_nav', 'tanggal_nav', 'nav_date']);
        if ($raw === null || $raw === '') return null;
        return $this->parseExcelDate($raw);
    }

    protected function parseNab(array $row): ?float
    {
        $raw = $this->pick($row, [
            'nab_per_unit', 'nab', 'nav', 'nav_per_unit', 'nilai_nav',
            'harga', 'up', 'nabup', 'nab/up',
        ]);
        if ($raw === null || $raw === '') return null;
        if (is_numeric($raw)) return (float) $raw;
        $clean = preg_replace('/[^\d,.-]/', '', (string) $raw);
        $clean = str_replace('.', '', $clean);
        $clean = str_replace(',', '.', $clean);
        return is_numeric($clean) ? (float) $clean : null;
    }

    protected function pick(array $row, array $keys): mixed
    {
        foreach ($keys as $key) {
            if (isset($row[$key]) && $row[$key] !== '' && $row[$key] !== null) {
                return $row[$key];
            }
        }
        return null;
    }
}
