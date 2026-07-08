<?php

namespace App\Imports;

use PhpOffice\PhpSpreadsheet\IOFactory;

class LegacyFormatReader
{
    public function read(string $filePath): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $data = [];

        foreach ($spreadsheet->getSheetNames() as $name) {
            $method = 'read' . str_replace(' ', '', $name);
            if (method_exists($this, $method)) {
                $this->$method($spreadsheet->getSheetByName($name), $data);
            }
        }

        return $data;
    }

    private function readPosisiKeuangan($sheet, array &$data): void
    {
        $rows = $sheet->toArray();
        foreach ($rows as $row) {
            $uraian = trim((string)($row[1] ?? ''));
            $val = $row[3] ?? '';
            switch ($uraian) {
                case 'Kas di bank':
                    $data['kas_dan_bank'] = $val;
                    break;
                case 'Piutang transaksi efek':
                    $data['piutang_transaksi_efek'] = $val;
                    break;
                case 'Piutang bunga':
                    $data['piutang_bunga'] = $val;
                    break;
                case 'Piutang dividen':
                    $data['piutang_dividen'] = $val;
                    break;
                case 'Piutang lain-lain':
                    $data['piutang_lain'] = $val;
                    break;
                case 'Jumlah portofolio efek':
                    $data['portofolio_efek'] = $val;
                    break;
                case 'Instrumen pasar uang':
                    $data['instrumen_pasar_uang'] = $val;
                    break;
                case 'Utang pajak':
                    $data['utang_pajak'] = $val;
                    break;
                case 'Utang lain-lain':
                    $data['utang_lain'] = $val;
                    break;
                case 'Uang muka diterima atas pemesanan unit penyertaan':
                    $data['uang_muka_diterima'] = $val;
                    break;
                case 'Liabilitas atas pembelian kembali unit penyertaan':
                    $data['liabilitas_pembelian_kembali'] = $val;
                    break;
                case 'Beban akrual':
                    $data['beban_akrual'] = $val;
                    break;
                case 'Liabilitas atas biaya pembelian kembali unit penyertaan':
                    $data['liabilitas_atas_biaya'] = $val;
                    break;
                case 'Utang transaksi efek':
                    $data['utang_lain'] = $val;
                    break;
            }
        }
    }

    private function readLabaRugi($sheet, array &$data): void
    {
        $rows = $sheet->toArray();
        foreach ($rows as $row) {
            $uraian = trim((string)($row[1] ?? ''));
            $val = $row[3] ?? '';
            switch ($uraian) {
                case 'Pendapatan bunga':
                    $data['pendapatan_bunga'] = $val;
                    break;
                case 'Pendapatan dividen':
                    $data['pendapatan_dividen'] = $val;
                    break;
                case 'Pendapatan lain-lain':
                    $data['pendapatan_lainnya'] = $val;
                    break;
                case 'Kerugian investasi yang telah direalisasi':
                    $data['gain_realized'] = $val;
                    break;
                case 'Keuntungan (kerugian) investasi yang belum direalisasi':
                    $data['gain_unrealized'] = $val;
                    break;
                case 'Beban pengelolaan investasi':
                    $data['beban_pengelolaan_investasi'] = $val;
                    break;
                case 'Beban kustodian':
                    $data['beban_kustodian'] = $val;
                    break;
                case 'Beban lain-lain':
                    $data['beban_lain'] = $val;
                    break;
                case 'JUMLAH BEBAN':
                    $data['total_beban'] = $val;
                    break;
                case 'LABA (RUGI) SEBELUM PAJAK':
                    $data['laba_sebelum_pajak'] = $val;
                    break;
                case 'BEBAN PAJAK':
                    $data['beban_pajak_penghasilan'] = $val;
                    break;
                case 'LABA (RUGI) TAHUN BERJALAN':
                    $data['laba_bersih_tahun_berjalan'] = $val;
                    break;
                case 'PENGHASILAN KOMPREHENSIF LAIN':
                    $data['penghasilan_komprehensif_lain'] = $val;
                    break;
                case 'JUMLAH PENGHASILAN (RUGI) KOMPREHENSIF TAHUN BERJALAN':
                    $data['penghasilan_komprehensif_tahun_berjalan'] = $val;
                    break;
            }
        }
    }

    private function readArusKas($sheet, array &$data): void
    {
        $rows = $sheet->toArray();
        foreach ($rows as $row) {
            $uraian = trim((string)($row[1] ?? ''));
            $val = $row[3] ?? '';
            switch ($uraian) {
                case 'Pembelian portofolio efek ekuitas':
                    $data['pembelian_efek_ekuitas'] = $val;
                    break;
                case 'Hasil penjualan portofolio efek ekuitas':
                    $data['penjualan_efek_ekuitas'] = $val;
                    break;
                case 'Penerimaan dari penjualan unit penyertaan':
                    $data['penerimaan_penjualan_unit'] = $val;
                    break;
                case 'Pembayaran untuk pembelian kembali unit penyertaan':
                    $data['pembayaran_pembelian_kembali_unit'] = $val;
                    break;
                case 'KAS DI BANK AWAL TAHUN':
                    $data['kas_awal_tahun'] = $val;
                    break;
                case 'KAS DI BANK AKHIR TAHUN':
                    $data['kas_akhir_tahun'] = $val;
                    break;
                case 'Penerimaan bunga - bersih':
                    $data['penerimaan_bunga_deposito'] = $val;
                    break;
            }
        }
    }

    private function readRingkasan($sheet, array &$data): void
    {
        $rows = $sheet->toArray();

        $infoMap = [
            'Periode laporan' => 'tahun_laporan',
            'Tanggal konversi' => 'tanggal_data',
        ];
        foreach ($rows as $row) {
            $label = trim((string)($row[0] ?? ''));
            $value = trim((string)($row[1] ?? ''));
            if (isset($infoMap[$label])) {
                $data[$infoMap[$label]] = $value;
            }
        }

        $indicatorMap = [
            'Jumlah Aset' => 'total_aset',
            'Jumlah Liabilitas' => 'total_liabilitas',
            'Nilai Aset Bersih' => 'nilai_aset_bersih',
            'Jumlah Unit Penyertaan Beredar' => 'total_unit_beredar',
            'NAB per Unit Penyertaan' => 'nab_per_unit',
            'Jumlah Pendapatan (Kerugian) - Bersih' => 'total_pendapatan',
            'Laba (Rugi) Tahun Berjalan' => 'laba_bersih_tahun_berjalan',
            'Kas Bersih dari Aktivitas Operasi' => 'arus_kas_operasi',
            'Kas Bersih dari Aktivitas Pendanaan' => 'arus_kas_pendanaan',
            'Kas di Bank Akhir Tahun' => 'kas_akhir_tahun',
        ];

        $foundHeader = false;
        foreach ($rows as $row) {
            $label = trim((string)($row[0] ?? ''));
            if ($label === 'Indikator') {
                $foundHeader = true;
                continue;
            }
            if ($foundHeader && isset($indicatorMap[$label])) {
                $data[$indicatorMap[$label]] = $row[1] ?? '';
            }
        }
    }

    private function readPerubahanAsetBersih($sheet, array &$data): void
    {
    }
}
