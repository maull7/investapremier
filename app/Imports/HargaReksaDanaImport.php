<?php

namespace App\Imports;

use App\Models\ReksaDana;
use App\Models\InvestmentManager;
use App\Services\KodeGeneratorService;
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

        $nama = trim($row['nama_reksa_dana']);

        // Cari existing: prioritas kode, fallback nama
        $existing = null;
        if (!empty($row['kode_reksa_dana'])) {
            $existing = ReksaDana::where('kode_reksa_dana', trim($row['kode_reksa_dana']))->first();
        }
        if (!$existing) {
            $existing = ReksaDana::where('nama_reksa_dana', $nama)->first();
        }

        if ($existing) {
            $updateData = [
                'nama_reksa_dana'        => $nama,
                'nama_manajer_investasi' => trim($row['nama_manajer_investasi'] ?? ''),
                'jenis'                  => trim($row['jenis'] ?? ''),
                'kategori'               => array_filter($kategori),
                'mata_uang'              => strtoupper(trim($row['mata_uang'] ?? 'IDR')),
                'nab_per_unit'           => $row['nab_per_unit'] ?? null,
                'tanggal_nab'            => $tanggal,
            ];
            if (!empty($row['kode_reksa_dana'])) {
                $updateData['kode_reksa_dana'] = trim($row['kode_reksa_dana']);
            }
            $existing->update($updateData);
            return $existing;
        }

        $kategoriProduk = trim($row['kategori_produk'] ?? '');
        $data = [
            'nama_manajer_investasi' => trim($row['nama_manajer_investasi'] ?? ''),
            'jenis'                  => trim($row['jenis'] ?? ''),
            'kategori'               => array_filter($kategori),
            'kategori_produk'        => $kategoriProduk ?: null,
            'mata_uang'              => strtoupper(trim($row['mata_uang'] ?? 'IDR')),
            'nab_per_unit'           => $row['nab_per_unit'] ?? null,
            'tanggal_nab'            => $tanggal,
        ];

        if (!empty($row['kode_reksa_dana'])) {
            $data['kode_reksa_dana'] = trim($row['kode_reksa_dana']);
        } else {
            $data['kode_reksa_dana'] = $this->generateKodeReksaDana($data);
        }

        return ReksaDana::create(array_merge(['nama_reksa_dana' => $nama], $data));
    }

    private function generateKodeReksaDana(array $data): ?string
    {
        if (empty($data['nama_manajer_investasi']) || empty($data['jenis'])) return null;

        $manager = InvestmentManager::where('name', $data['nama_manajer_investasi'])
            ->whereNotNull('kode_mi')
            ->first();

        if (!$manager || !$manager->kode_mi) return null;

        return app(KodeGeneratorService::class)->generateKodeReksaDana(
            $manager->kode_mi,
            $data['jenis'],
            $data['kategori_produk'] ?? null
        );
    }
}
