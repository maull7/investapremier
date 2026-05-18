<?php

namespace App\Imports;

use App\Models\ReksaDana;
use App\Models\HargaReksaDana;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

/**
 * Upload Harian Reksa Dana
 * Kolom: nama_reksa_dana | tanggal | nab_per_unit | total_dana_kelolaan | unit_penyertaan
 */
class HarianReksaDanaImport implements ToModel, WithHeadingRow, SkipsEmptyRows
{
    public function model(array $row): ?HargaReksaDana
    {
        if (empty($row['nama_reksa_dana']) || empty($row['tanggal'])) return null;

        $reksaDana = ReksaDana::where('nama_reksa_dana', trim($row['nama_reksa_dana']))->first();
        if (!$reksaDana) return null;

        try {
            $tanggal = \Carbon\Carbon::parse($row['tanggal'])->toDateString();
        } catch (\Exception $e) {
            return null;
        }

        // Update NAB terbaru di master jika tanggal lebih baru
        if (!$reksaDana->tanggal_nab || $tanggal >= $reksaDana->tanggal_nab->toDateString()) {
            $reksaDana->update([
                'nab_per_unit' => $row['nab_per_unit'],
                'tanggal_nab'  => $tanggal,
            ]);
        }

        return HargaReksaDana::updateOrCreate(
            ['reksa_dana_id' => $reksaDana->id, 'tanggal' => $tanggal],
            ['nab_per_unit' => $row['nab_per_unit']]
        );
    }
}
