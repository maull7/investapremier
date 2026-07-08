<?php

namespace App\Imports;

use PhpOffice\PhpSpreadsheet\IOFactory;

class LegacyFormatReader
{
    public function read(string $filePath): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $data = [
            'data_tahunan' => [
                'years' => [],
            ],
        ];

        foreach ($spreadsheet->getSheetNames() as $name) {
            $method = 'read' . str_replace(' ', '', $name);
            if (method_exists($this, $method)) {
                $this->$method($spreadsheet->getSheetByName($name), $data);
            }
        }

        // Flatten latest year values to scalar fields for backward compatibility
        $data = $this->flattenLatestYear($data);

        return $data;
    }

    private function detectYears(array $headerRow): array
    {
        $years = [];
        foreach ($headerRow as $idx => $cell) {
            if ($idx < 3) continue;
            $year = $this->extractYear($cell);
            if ($year) {
                $years[$idx] = $year;
            }
        }
        return $years;
    }

    private function extractYear($value): ?int
    {
        $value = trim((string) $value);
        if (preg_match('/(\d{4})/', $value, $matches)) {
            return (int) $matches[1];
        }
        return null;
    }

    private function getValuesByYear(array $row, array $yearMap): array
    {
        $values = [];
        foreach ($yearMap as $idx => $year) {
            $val = $row[$idx] ?? '';
            $values[$year] = $this->normalizeNumber($val);
        }
        return $values;
    }

    private function normalizeNumber($value)
    {
        if ($value === null || $value === '') return null;
        if (is_numeric($value)) return $value;

        $str = trim((string) $value);
        if ($str === '' || $str === '-') return null;

        $isNegative = false;
        if (str_starts_with($str, '(') && str_ends_with($str, ')')) {
            $isNegative = true;
            $str = trim($str, '()');
        } elseif (str_starts_with($str, '-')) {
            $isNegative = true;
            $str = ltrim($str, '-');
        }

        // Indonesian format: 1.234.567,89
        $str = str_replace('.', '', $str);
        $str = str_replace(',', '.', $str);

        if (!is_numeric($str)) return null;

        $num = (float) $str;
        return $isNegative ? -$num : $num;
    }

    private function setDataTahunan(array &$data, string $key, array $yearValues): void
    {
        foreach ($yearValues as $year => $value) {
            if ($value !== null && $value !== '') {
                $yearStr = (string) $year;
                if (!isset($data['data_tahunan'][$yearStr])) {
                    $data['data_tahunan'][$yearStr] = [];
                }
                $data['data_tahunan'][$yearStr][$key] = $value;
            }
        }
    }

    private function flattenLatestYear(array $data): array
    {
        $years = $data['data_tahunan']['years'] ?? [];
        if (empty($years)) {
            return $data;
        }

        $latestYear = (string) $years[0];
        $latest = $data['data_tahunan'][$latestYear] ?? [];

        $map = [
            'portofolio_efek' => 'portofolio_efek',
            'kas_dan_bank' => 'kas_dan_bank',
            'piutang_transaksi_efek' => 'piutang_transaksi_efek',
            'piutang_bunga' => 'piutang_bunga',
            'piutang_dividen' => 'piutang_dividen',
            'piutang_lain' => 'piutang_lain',
            'instrumen_pasar_uang' => 'instrumen_pasar_uang',
            'total_unit_beredar' => 'total_unit_beredar',
            'nab_per_unit' => 'nab_per_unit',
            'total_aset' => 'total_aset',
            'uang_muka_diterima' => 'uang_muka_diterima',
            'liabilitas_pembelian_kembali' => 'liabilitas_pembelian_kembali',
            'beban_akrual' => 'beban_akrual',
            'liabilitas_atas_biaya' => 'liabilitas_atas_biaya',
            'utang_pajak' => 'utang_pajak',
            'utang_lain' => 'utang_lain',
            'nilai_aset_bersih' => 'nilai_aset_bersih',
            'total_liabilitas' => 'total_liabilitas',
            'pendapatan_bunga' => 'pendapatan_bunga',
            'pendapatan_dividen' => 'pendapatan_dividen',
            'pendapatan_lainnya' => 'pendapatan_lainnya',
            'gain_realized' => 'gain_realized',
            'gain_unrealized' => 'gain_unrealized',
            'total_pendapatan' => 'total_pendapatan',
            'beban_pengelolaan_investasi' => 'beban_pengelolaan_investasi',
            'beban_kustodian' => 'beban_kustodian',
            'beban_lain' => 'beban_lain',
            'total_beban' => 'total_beban',
            'laba_sebelum_pajak' => 'laba_sebelum_pajak',
            'beban_pajak_penghasilan' => 'beban_pajak_penghasilan',
            'laba_bersih_tahun_berjalan' => 'laba_bersih_tahun_berjalan',
            'penghasilan_komprehensif_lain' => 'penghasilan_komprehensif_lain',
            'penghasilan_komprehensif_tahun_berjalan' => 'penghasilan_komprehensif_tahun_berjalan',
            'laba_bersih' => 'laba_bersih',
            'penerimaan_bunga_deposito' => 'penerimaan_bunga_deposito',
            'penerimaan_dividen_kas' => 'penerimaan_dividen_kas',
            'penjualan_efek_ekuitas' => 'penjualan_efek_ekuitas',
            'pembelian_efek_ekuitas' => 'pembelian_efek_ekuitas',
            'beban_investasi' => 'beban_investasi',
            'arus_kas_operasi' => 'arus_kas_operasi',
            'penerimaan_penjualan_unit' => 'penerimaan_penjualan_unit',
            'pembayaran_pembelian_kembali_unit' => 'pembayaran_pembelian_kembali_unit',
            'arus_kas_pendanaan' => 'arus_kas_pendanaan',
            'kas_awal_tahun' => 'kas_awal_tahun',
            'kas_akhir_tahun' => 'kas_akhir_tahun',
        ];

        foreach ($map as $key => $scalarKey) {
            $value = $latest[$key] ?? null;
            if ($value !== null && $value !== '') {
                $data[$scalarKey] = $value;
            }
        }

        return $data;
    }

    private function readPosisiKeuangan($sheet, array &$data): void
    {
        $rows = $sheet->toArray();
        if (empty($rows[3])) return;

        $yearMap = $this->detectYears($rows[3]);
        $years = array_values($yearMap);
        if (!empty($years)) {
            $existing = $data['data_tahunan']['years'] ?? [];
            $data['data_tahunan']['years'] = array_values(array_unique(array_merge($existing, $years)));
            rsort($data['data_tahunan']['years']);
        }

        $map = [
            'Kas di bank' => 'kas_dan_bank',
            'Piutang transaksi efek' => 'piutang_transaksi_efek',
            'Piutang bunga' => 'piutang_bunga',
            'Piutang dividen' => 'piutang_dividen',
            'Piutang lain-lain' => 'piutang_lain',
            'Jumlah portofolio efek' => 'portofolio_efek',
            'Instrumen pasar uang' => 'instrumen_pasar_uang',
            'Utang pajak' => 'utang_pajak',
            'Utang lain-lain' => 'utang_lain',
            'Uang muka diterima atas pemesanan unit penyertaan' => 'uang_muka_diterima',
            'Liabilitas atas pembelian kembali unit penyertaan' => 'liabilitas_pembelian_kembali',
            'Beban akrual' => 'beban_akrual',
            'Liabilitas atas biaya pembelian kembali unit penyertaan' => 'liabilitas_atas_biaya',
            'JUMLAH ASET' => 'total_aset',
            'JUMLAH LIABILITAS' => 'total_liabilitas',
            'NILAI ASET BERSIH' => 'nilai_aset_bersih',
            'JUMLAH UNIT PENYERTAAN BEREDAR' => 'total_unit_beredar',
            'NILAI ASET BERSIH PER UNIT PENYERTAAN' => 'nab_per_unit',
        ];

        foreach ($rows as $row) {
            $uraian = trim((string)($row[1] ?? ''));
            if (!isset($map[$uraian])) continue;
            $this->setDataTahunan($data, $map[$uraian], $this->getValuesByYear($row, $yearMap));
        }
    }

    private function readLabaRugi($sheet, array &$data): void
    {
        $rows = $sheet->toArray();
        if (empty($rows[3])) return;

        $yearMap = $this->detectYears($rows[3]);
        $years = array_values($yearMap);
        if (!empty($years)) {
            $existing = $data['data_tahunan']['years'] ?? [];
            $data['data_tahunan']['years'] = array_values(array_unique(array_merge($existing, $years)));
            rsort($data['data_tahunan']['years']);
        }

        $map = [
            'Pendapatan bunga' => 'pendapatan_bunga',
            'Pendapatan dividen' => 'pendapatan_dividen',
            'Pendapatan lain-lain' => 'pendapatan_lainnya',
            'Kerugian investasi yang telah direalisasi' => 'gain_realized',
            'Keuntungan (kerugian) investasi yang belum direalisasi' => 'gain_unrealized',
            'Beban pengelolaan investasi' => 'beban_pengelolaan_investasi',
            'Beban kustodian' => 'beban_kustodian',
            'Beban lain-lain' => 'beban_lain',
            'JUMLAH BEBAN' => 'total_beban',
            'LABA (RUGI) SEBELUM PAJAK' => 'laba_sebelum_pajak',
            'BEBAN PAJAK' => 'beban_pajak_penghasilan',
            'LABA (RUGI) TAHUN BERJALAN' => 'laba_bersih_tahun_berjalan',
            'PENGHASILAN KOMPREHENSIF LAIN' => 'penghasilan_komprehensif_lain',
            'JUMLAH PENGHASILAN (RUGI) KOMPREHENSIF TAHUN BERJALAN' => 'penghasilan_komprehensif_tahun_berjalan',
            'JUMLAH PENDAPATAN (KERUGIAN) - BERSIH' => 'total_pendapatan',
            'LABA BERSIH' => 'laba_bersih',
        ];

        foreach ($rows as $row) {
            $uraian = trim((string)($row[1] ?? ''));
            if (!isset($map[$uraian])) continue;
            $this->setDataTahunan($data, $map[$uraian], $this->getValuesByYear($row, $yearMap));
        }
    }

    private function readArusKas($sheet, array &$data): void
    {
        $rows = $sheet->toArray();
        if (empty($rows[3])) return;

        $yearMap = $this->detectYears($rows[3]);
        $years = array_values($yearMap);
        if (!empty($years)) {
            $existing = $data['data_tahunan']['years'] ?? [];
            $data['data_tahunan']['years'] = array_values(array_unique(array_merge($existing, $years)));
            rsort($data['data_tahunan']['years']);
        }

        $map = [
            'Pembelian portofolio efek ekuitas' => 'pembelian_efek_ekuitas',
            'Hasil penjualan portofolio efek ekuitas' => 'penjualan_efek_ekuitas',
            'Penerimaan dari penjualan unit penyertaan' => 'penerimaan_penjualan_unit',
            'Pembayaran untuk pembelian kembali unit penyertaan' => 'pembayaran_pembelian_kembali_unit',
            'KAS DI BANK AWAL TAHUN' => 'kas_awal_tahun',
            'KAS DI BANK AKHIR TAHUN' => 'kas_akhir_tahun',
            'Penerimaan bunga - bersih' => 'penerimaan_bunga_deposito',
            'Pembayaran beban investasi' => 'beban_investasi',
            'Kas Bersih Diperoleh dari (Digunakan untuk) Aktivitas Operasi' => 'arus_kas_operasi',
            'Kas Bersih Diperoleh dari (Digunakan untuk) Aktivitas Pendanaan' => 'arus_kas_pendanaan',
        ];

        foreach ($rows as $row) {
            $uraian = trim((string)($row[1] ?? ''));
            if (!isset($map[$uraian])) continue;
            $this->setDataTahunan($data, $map[$uraian], $this->getValuesByYear($row, $yearMap));
        }
    }

    private function readRingkasan($sheet, array &$data): void
    {
        $rows = $sheet->toArray();

        foreach ($rows as $row) {
            $label = trim((string)($row[0] ?? ''));
            if ($label === 'Periode laporan') {
                $data['tahun_laporan'] = $this->extractYear($row[1] ?? '') ?: ($row[1] ?? null);
            }
            if ($label === 'Tanggal konversi') {
                $data['tanggal_data'] = $row[1] ?? null;
            }
        }
    }

    private function readPerubahanAsetBersih($sheet, array &$data): void
    {
        // Placeholder: data PAB diambil dari sheet lain
    }
}
