<?php

namespace App\Imports;

use App\Models\ReksaDana;
use App\Models\HargaReksaDana;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

/**
 * Upload Harian Reksa Dana
 * Kolom: kode_reksa_dana (opsional) | nama_reksa_dana | tanggal | nab_per_unit | total_dana_kelolaan | unit_penyertaan
 */
class HarianReksaDanaImport implements ToModel, WithHeadingRow, SkipsEmptyRows
{
    public function model(array $row): ?HargaReksaDana
    {
        if (empty($row['tanggal']) || empty($row['nab_per_unit'])) return null;

        $reksaDana = $this->findReksaDana($row);
        if (!$reksaDana) return null;

        $tanggal = $this->parseDate($row['tanggal']);
        if (!$tanggal) return null;

        if (!$reksaDana->tanggal_nab || $tanggal >= $reksaDana->tanggal_nab->toDateString()) {
            $reksaDana->update([
                'nab_per_unit' => $row['nab_per_unit'],
                'tanggal_nab'  => $tanggal,
            ]);
        }

        $aum = is_numeric($row['total_dana_kelolaan'] ?? null) ? $row['total_dana_kelolaan'] : null;
        $up  = is_numeric($row['unit_penyertaan'] ?? null)     ? $row['unit_penyertaan']     : null;

        return HargaReksaDana::updateOrCreate(
            ['reksa_dana_id' => $reksaDana->id, 'tanggal' => $tanggal],
            ['nab_per_unit' => $row['nab_per_unit'], 'aum' => $aum, 'unit_participation' => $up]
        );
    }

    private function findReksaDana(array $row): ?ReksaDana
    {
        $kode = !empty($row['kode_reksa_dana']) ? trim($row['kode_reksa_dana']) : null;
        $nama = !empty($row['nama_reksa_dana']) ? trim($row['nama_reksa_dana']) : null;

        // Prioritas: kode_reksa_dana jika ada
        if ($kode) {
            $found = ReksaDana::where('kode_reksa_dana', $kode)->first();
            if ($found) return $found;
        }

        // Fallback: cari by nama
        if ($nama) {
            $found = ReksaDana::where('nama_reksa_dana', $nama)->first();
            if ($found) return $found;

            // Tidak ditemukan: buat baru
            return ReksaDana::create([
                'nama_reksa_dana'        => $nama,
                'kode_reksa_dana'        => $kode,
                'nama_manajer_investasi' => '',
                'jenis'                  => '',
                'kategori'               => [],
            ]);
        }

        return null;
    }

    private function parseDate(mixed $value): ?string
    {
        if (empty($value)) return null;

        // Excel date serial number (integer or float)
        if (is_numeric($value)) {
            try {
                return ExcelDate::excelToDateTimeObject((float) $value)->format('Y-m-d');
            } catch (\Throwable) {}
        }

        // String date
        try {
            return \Carbon\Carbon::parse((string) $value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }
}
