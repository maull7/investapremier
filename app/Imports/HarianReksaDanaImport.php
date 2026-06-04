<?php

namespace App\Imports;

use App\Models\ReksaDana;
use App\Models\HargaReksaDana;
use App\Services\KodeReksaDanaParser;
use App\Support\ExcelDateHelper;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

/**
 * Upload Harian Reksa Dana
 * Kolom: kode_reksa_dana (opsional) | nama_reksa_dana | tanggal | nab_per_unit | total_dana_kelolaan | unit_penyertaan
 */
class HarianReksaDanaImport implements ToModel, WithHeadingRow, SkipsEmptyRows
{
    use ExcelDateHelper;

    public int $imported = 0;
    public int $skipped = 0;

    public function model(array $row): ?HargaReksaDana
    {
        if (empty($row['tanggal']) || empty($row['nab_per_unit'])) {
            $this->skipped++;
            return null;
        }

        $reksaDana = $this->findReksaDana($row);
        if (!$reksaDana) {
            $this->skipped++;
            return null;
        }

        $tanggal = $this->parseExcelDate($row['tanggal']);
        if (!$tanggal) {
            $this->skipped++;
            return null;
        }

        if (!$reksaDana->tanggal_nab || $tanggal >= $reksaDana->tanggal_nab->toDateString()) {
            $reksaDana->update([
                'nab_per_unit' => $row['nab_per_unit'],
                'tanggal_nab'  => $tanggal,
            ]);
        }

        $aum = is_numeric($row['total_dana_kelolaan'] ?? null) ? $row['total_dana_kelolaan'] : null;
        $up  = is_numeric($row['unit_penyertaan'] ?? null)     ? $row['unit_penyertaan']     : null;

        HargaReksaDana::updateOrCreate(
            ['reksa_dana_id' => $reksaDana->id, 'tanggal' => $tanggal],
            ['nab_per_unit' => $row['nab_per_unit'], 'aum' => $aum, 'unit_participation' => $up]
        );

        $this->imported++;
        return null;
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

            // Tidak ditemukan: buat baru, auto-fill dari kode jika bisa di-parse
            $data = [
                'nama_reksa_dana'        => $nama,
                'kode_reksa_dana'        => $kode,
                'nama_manajer_investasi' => '',
                'jenis'                  => '',
                'kategori'               => [],
            ];

            if ($kode) {
                $parsed = app(KodeReksaDanaParser::class)->parse($kode);
                if ($parsed) {
                    $data['nama_manajer_investasi'] = $parsed['nama_manajer_investasi'];
                    $data['jenis'] = $parsed['jenis'];
                    $data['kategori_produk'] = $parsed['kategori_produk'];
                    $data['kategori'] = $parsed['kategori'];
                    $data['kelas'] = $parsed['kelas'];
                    $data['mata_uang'] = $parsed['mata_uang'];
                }
            }

            return ReksaDana::create($data);
        }

        return null;
    }

}
