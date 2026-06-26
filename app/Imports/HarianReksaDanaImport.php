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

    public int $imported = 0;     // record unik yang berhasil diproses (created + updated)
    public int $created = 0;      // record harga baru yang dibuat
    public int $updated = 0;      // record harga existing yang diupdate
    public int $duplicates = 0;   // baris duplikat di Excel (kombinasi RD+tanggal sama)
    public int $skipped = 0;      // baris kosong/tidak valid

    /** Kombinasi reksa_dana_id|tanggal yang sudah pernah diproses dalam import ini. */
    private array $processedKeys = [];

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

        $key = $reksaDana->id . '|' . $tanggal;
        if (in_array($key, $this->processedKeys, true)) {
            $this->duplicates++;
        } else {
            $this->processedKeys[] = $key;
            $exists = HargaReksaDana::where('reksa_dana_id', $reksaDana->id)
                ->where('tanggal', $tanggal)
                ->exists();
            $this->imported++;
            if ($exists) {
                $this->updated++;
            } else {
                $this->created++;
            }
        }

        HargaReksaDana::updateOrCreate(
            ['reksa_dana_id' => $reksaDana->id, 'tanggal' => $tanggal],
            ['nab_per_unit' => $row['nab_per_unit'], 'aum' => $aum, 'unit_participation' => $up]
        );

        return null;
    }

    private function findReksaDana(array $row): ?ReksaDana
    {
        $kode = !empty($row['kode_reksa_dana']) ? trim($row['kode_reksa_dana']) : null;
        $nama = !empty($row['nama_reksa_dana']) ? trim($row['nama_reksa_dana']) : null;

        $parser = app(KodeReksaDanaParser::class);
        $kodeValid = $kode && $parser->isValidKode($kode);

        // Prioritas: kode_reksa_dana jika ada dan valid
        if ($kodeValid) {
            $found = ReksaDana::where('kode_reksa_dana', $kode)->first();
            if ($found) return $found;
        }

        // Fallback: cari by nama
        if ($nama) {
            $found = ReksaDana::where('nama_reksa_dana', $nama)->first();
            if ($found) {
                // Jika existing punya kode tidak valid, tandai untuk diperbaiki nanti
                if (!$kode && $found->kode_reksa_dana && !$parser->isValidKode($found->kode_reksa_dana)) {
                    // Hapus kode invalid, biarkan diisi ulang via sync/import berikutnya
                    $found->update(['kode_reksa_dana' => null]);
                }
                return $found;
            }

            // Tidak ditemukan: buat baru, auto-fill dari kode jika valid
            $data = [
                'nama_reksa_dana'        => $nama,
                'kode_reksa_dana'        => $kodeValid ? $kode : null,
                'nama_manajer_investasi' => '',
                'jenis'                  => '',
                'kategori'               => [],
            ];

            if ($kodeValid) {
                $parsedFromKode = $parser->databaseAttributes($kode);
                foreach ($parsedFromKode as $key => $value) {
                    if (empty($data[$key])) {
                        $data[$key] = $value;
                    }
                }
            }

            return ReksaDana::create($data);
        }

        return null;
    }

}
