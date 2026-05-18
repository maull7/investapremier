<?php

namespace App\Imports;

use App\Models\ReksaDana;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

/**
 * Upload Harga Reksa Dana
 * Kolom: nama_reksa_dana | nama_manajer_investasi | jenis | kategori (pisah koma) | mata_uang | nab_per_unit | tanggal_nab
 */
class HargaReksaDanaImport implements ToModel, WithHeadingRow, SkipsEmptyRows
{
    public function model(array $row): ?ReksaDana
    {
        if (empty($row['nama_reksa_dana'])) return null;

        $kategori = array_map('trim', explode(',', $row['kategori'] ?? ''));

        $tanggal = null;
        if (!empty($row['tanggal_nab'])) {
            try {
                $tanggal = \Carbon\Carbon::parse($row['tanggal_nab'])->toDateString();
            } catch (\Exception $e) {
                $tanggal = null;
            }
        }

        return ReksaDana::updateOrCreate(
            ['nama_reksa_dana' => trim($row['nama_reksa_dana'])],
            [
                'nama_manajer_investasi' => trim($row['nama_manajer_investasi'] ?? ''),
                'jenis'                  => trim($row['jenis'] ?? ''),
                'kategori'               => array_filter($kategori),
                'mata_uang'              => strtoupper(trim($row['mata_uang'] ?? 'IDR')),
                'nab_per_unit'           => $row['nab_per_unit'] ?? null,
                'tanggal_nab'            => $tanggal,
            ]
        );
    }
}
