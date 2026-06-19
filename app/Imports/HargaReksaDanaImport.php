<?php

namespace App\Imports;

use App\Support\ExcelDateHelper;
use App\Models\HargaReksaDana;
use App\Models\ReksaDana;
use App\Models\InvestmentManager;
use App\Services\KodeGeneratorService;
use App\Services\KodeReksaDanaParser;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;

/**
 * Upload Harga Reksa Dana
 * Kolom: nama_reksa_dana | nama_manajer_investasi | jenis | kategori (pisah koma) | mata_uang | nab_per_unit | tanggal_nab
 */
class HargaReksaDanaImport implements ToModel, WithHeadingRow, SkipsEmptyRows, WithCalculatedFormulas
{
    use ExcelDateHelper;

    public int $imported = 0;
    public int $skipped = 0;

    private function resolveInvestmentManagerId(string $nama): ?int
    {
        return InvestmentManager::where('name', $nama)->value('id');
    }

    public function model(array $row): ?ReksaDana
    {
        if (empty($row['nama_reksa_dana'])) {
            $this->skipped++;
            return null;
        }

        $kategori = array_map('trim', explode(',', $row['kategori'] ?? ''));

        $tanggal = $this->parseExcelDate($row['tanggal_nab'] ?? null);

        $nama = trim($row['nama_reksa_dana']);
        $kode = !empty($row['kode_reksa_dana']) ? trim($row['kode_reksa_dana']) : null;

        // Cari existing: prioritas kode, fallback nama
        $existing = null;
        if ($kode) {
            $existing = ReksaDana::where('kode_reksa_dana', $kode)->first();
        }
        if (!$existing) {
            $existing = ReksaDana::where('nama_reksa_dana', $nama)->first();
        }

        $investmentManagerId = !empty($row['nama_manajer_investasi'])
            ? $this->resolveInvestmentManagerId(trim($row['nama_manajer_investasi']))
            : null;

        if ($existing) {
            $updateData = [
                'nama_reksa_dana'        => $nama,
                'nama_manajer_investasi' => trim($row['nama_manajer_investasi'] ?? ''),
                'investment_manager_id'  => $investmentManagerId,
                'jenis'                  => trim($row['jenis'] ?? ''),
                'kategori'               => array_filter($kategori),
                'mata_uang'              => strtoupper(trim($row['mata_uang'] ?? 'IDR')),
                'nab_per_unit'           => $row['nab_per_unit'] ?? null,
                'tanggal_nab'            => $tanggal,
            ];
            if ($kode) {
                $updateData['kode_reksa_dana'] = $kode;
            }

            if ($kode) {
                $updateData = array_merge($updateData, app(KodeReksaDanaParser::class)->databaseAttributes($kode));
            }

            $existing->update($updateData);

            if ($tanggal && !empty($row['nab_per_unit'])) {
                HargaReksaDana::updateOrCreate(
                    ['reksa_dana_id' => $existing->id, 'tanggal' => $tanggal],
                    ['nab_per_unit' => $row['nab_per_unit']]
                );
            }

            $this->imported++;
            return $existing;
        }

        $kategoriProduk = trim($row['kategori_produk'] ?? '');
        $data = [
            'nama_manajer_investasi' => trim($row['nama_manajer_investasi'] ?? ''),
            'investment_manager_id'  => $investmentManagerId,
            'jenis'                  => trim($row['jenis'] ?? ''),
            'kategori'               => array_filter($kategori),
            'kategori_produk'        => $kategoriProduk ?: null,
            'mata_uang'              => strtoupper(trim($row['mata_uang'] ?? 'IDR')),
            'nab_per_unit'           => $row['nab_per_unit'] ?? null,
            'tanggal_nab'            => $tanggal,
        ];

        if ($kode) {
            $data['kode_reksa_dana'] = $kode;

            $data = array_merge($data, app(KodeReksaDanaParser::class)->databaseAttributes($kode));
        } else {
            $data['kode_reksa_dana'] = $this->generateKodeReksaDana($data);
        }

        $reksaDana = ReksaDana::create(array_merge(['nama_reksa_dana' => $nama], $data));

        if ($tanggal && !empty($row['nab_per_unit'])) {
            HargaReksaDana::updateOrCreate(
                ['reksa_dana_id' => $reksaDana->id, 'tanggal' => $tanggal],
                ['nab_per_unit' => $row['nab_per_unit']]
            );
        }

        $this->imported++;
        return $reksaDana;
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
            $data['kategori_produk'] ?? null,
            null,
            $data['kategori'] ?? [],
            $data['mata_uang'] ?? 'IDR'
        );
    }
}
