<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class AnalisaExcelExport
{
    public function export(array $data, string $outputPath): void
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setTitle('Analisa Reksa Dana')
            ->setCreator('Analisa System');

        $this->buildPosisiKeuangan($spreadsheet, $data);
        $this->buildLabaRugi($spreadsheet, $data);
        $this->buildPerubahanAsetBersih($spreadsheet, $data);
        $this->buildArusKas($spreadsheet, $data);
        $this->buildRingkasan($spreadsheet, $data);

        $writer = new Xlsx($spreadsheet);
        $writer->save($outputPath);
        $spreadsheet->disconnectWorksheets();
    }

    private function getYears(array $data): array
    {
        $years = $data['data_tahunan']['years'] ?? [];
        if (empty($years)) {
            return [2025, 2024];
        }
        return $years;
    }

    private function getYearValue(array $data, string $key, string $year)
    {
        return $data['data_tahunan'][$year][$key] ?? null;
    }

    private function set(array &$data, string $key, $default = null)
    {
        $v = $data[$key] ?? $default;
        return ($v === '' || $v === null) ? $default : $v;
    }

    private function yearHeader(array $years): array
    {
        $header = ['Kategori', 'Uraian', 'Catatan'];
        foreach ($years as $year) {
            $header[] = (string) $year;
        }
        return $header;
    }

    private function row(?string $category, string $uraian, string $key, array $data): array
    {
        $row = [$category, $uraian, null];
        foreach ($this->getYears($data) as $year) {
            $row[] = $this->getYearValue($data, $key, (string) $year);
        }
        return $row;
    }

    private function emptyRow(array $years): array
    {
        return array_fill(0, 3 + count($years), null);
    }

    private function boldHeader($sheet, int $row, int $maxCol): void
    {
        $sheet->getStyle("A{$row}:" . chr(64 + $maxCol) . "{$row}")
            ->getFont()->setBold(true);
    }

    private function setupSheet($sheet, string $title, array $years): void
    {
        $sheet->setTitle($title);
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(50);
        $sheet->getColumnDimension('C')->setWidth(12);
        $yearCount = count($years);
        for ($i = 0; $i < $yearCount; $i++) {
            $sheet->getColumnDimension(chr(68 + $i))->setWidth(18);
        }
    }

    private function buildPosisiKeuangan(Spreadsheet $spreadsheet, array $data): void
    {
        $sheet = $spreadsheet->getActiveSheet();
        $years = $this->getYears($data);
        $this->setupSheet($sheet, 'Posisi Keuangan', $years);

        $rows = [
            $this->emptyRow($years),
            $this->emptyRow($years),
            $this->emptyRow($years),
            $this->yearHeader($years),
            ['ASET', null, null, ...array_fill(0, count($years), null)],
            ['Portofolio efek', 'Efek ekuitas', null, ...array_fill(0, count($years), null)],
            ['Portofolio efek', 'Instrumen pasar uang', null, ...array_fill(0, count($years), null)],
            $this->row('Portofolio efek', 'Jumlah portofolio efek', 'portofolio_efek', $data),
            $this->row('ASET', 'Kas di bank', 'kas_dan_bank', $data),
            $this->row('ASET', 'Piutang transaksi efek', 'piutang_transaksi_efek', $data),
            $this->row('ASET', 'Piutang bunga', 'piutang_bunga', $data),
            $this->row('ASET', 'Piutang dividen', 'piutang_dividen', $data),
            $this->row('ASET', 'Piutang lain-lain', 'piutang_lain', $data),
            ['ASET', 'JUMLAH ASET', null, ...array_fill(0, count($years), null)],
            $this->emptyRow($years),
            ['LIABILITAS', null, null, ...array_fill(0, count($years), null)],
            ['LIABILITAS', 'Pendapatan yang belum didistribusikan', null, ...array_fill(0, count($years), null)],
            $this->row('LIABILITAS', 'Uang muka diterima atas pemesanan unit penyertaan', 'uang_muka_diterima', $data),
            ['LIABILITAS', 'Utang transaksi efek', null, ...array_fill(0, count($years), null)],
            $this->row('LIABILITAS', 'Liabilitas atas pembelian kembali unit penyertaan', 'liabilitas_pembelian_kembali', $data),
            $this->row('LIABILITAS', 'Beban akrual', 'beban_akrual', $data),
            $this->row('LIABILITAS', 'Liabilitas atas biaya pembelian kembali unit penyertaan', 'liabilitas_atas_biaya', $data),
            $this->row('LIABILITAS', 'Utang pajak', 'utang_pajak', $data),
            $this->row('LIABILITAS', 'Utang lain-lain', 'utang_lain', $data),
            ['LIABILITAS', 'JUMLAH LIABILITAS', null, ...array_fill(0, count($years), null)],
            $this->row(null, 'NILAI ASET BERSIH', 'nilai_aset_bersih', $data),
            $this->emptyRow($years),
            $this->row('UNIT', 'JUMLAH UNIT PENYERTAAN BEREDAR', 'total_unit_beredar', $data),
            $this->row('UNIT', 'NILAI ASET BERSIH PER UNIT PENYERTAAN', 'nab_per_unit', $data),
            $this->emptyRow($years),
            $this->emptyRow($years),
        ];

        $sheet->fromArray($rows, null, 'A1');
        $this->boldHeader($sheet, 4, 3 + count($years));
    }

    private function buildLabaRugi(Spreadsheet $spreadsheet, array $data): void
    {
        $sheet = $spreadsheet->createSheet();
        $years = $this->getYears($data);
        $this->setupSheet($sheet, 'Laba Rugi', $years);

        $rows = [
            $this->emptyRow($years),
            $this->emptyRow($years),
            $this->emptyRow($years),
            $this->yearHeader($years),
            ['PENDAPATAN', null, null, ...array_fill(0, count($years), null)],
            $this->row('Pendapatan Investasi', 'Pendapatan bunga', 'pendapatan_bunga', $data),
            $this->row('Pendapatan Investasi', 'Pendapatan dividen', 'pendapatan_dividen', $data),
            $this->row('Pendapatan Investasi', 'Kerugian investasi yang telah direalisasi', 'gain_realized', $data),
            $this->row('Pendapatan Investasi', 'Keuntungan (kerugian) investasi yang belum direalisasi', 'gain_unrealized', $data),
            $this->row('Pendapatan Investasi', 'Pendapatan lain-lain', 'pendapatan_lainnya', $data),
            $this->row('PENDAPATAN', 'JUMLAH PENDAPATAN (KERUGIAN) - BERSIH', 'total_pendapatan', $data),
            $this->emptyRow($years),
            ['BEBAN', null, null, ...array_fill(0, count($years), null)],
            $this->row('Beban Investasi', 'Beban pengelolaan investasi', 'beban_pengelolaan_investasi', $data),
            $this->row('Beban Investasi', 'Beban kustodian', 'beban_kustodian', $data),
            $this->row('Beban Investasi', 'Beban lain-lain', 'beban_lain', $data),
            $this->row('BEBAN', 'JUMLAH BEBAN', 'total_beban', $data),
            $this->row(null, 'LABA (RUGI) SEBELUM PAJAK', 'laba_sebelum_pajak', $data),
            $this->row(null, 'BEBAN PAJAK', 'beban_pajak_penghasilan', $data),
            $this->row(null, 'LABA (RUGI) TAHUN BERJALAN', 'laba_bersih_tahun_berjalan', $data),
            $this->row(null, 'PENGHASILAN KOMPREHENSIF LAIN', 'penghasilan_komprehensif_lain', $data),
            $this->row(null, 'JUMLAH PENGHASILAN (RUGI) KOMPREHENSIF TAHUN BERJALAN', 'penghasilan_komprehensif_tahun_berjalan', $data),
            $this->emptyRow($years),
            $this->emptyRow($years),
        ];

        $sheet->fromArray($rows, null, 'A1');
        $this->boldHeader($sheet, 4, 3 + count($years));
    }

    private function buildArusKas(Spreadsheet $spreadsheet, array $data): void
    {
        $sheet = $spreadsheet->createSheet();
        $years = $this->getYears($data);
        $sheet->setTitle('Arus Kas');
        $sheet->getColumnDimension('A')->setWidth(12);
        $sheet->getColumnDimension('B')->setWidth(55);
        $sheet->getColumnDimension('C')->setWidth(12);
        for ($i = 0; $i < count($years); $i++) {
            $sheet->getColumnDimension(chr(68 + $i))->setWidth(18);
        }

        $rows = [
            $this->emptyRow($years),
            $this->emptyRow($years),
            $this->emptyRow($years),
            $this->yearHeader($years),
            ['OPERASI', null, null, ...array_fill(0, count($years), null)],
            $this->row('OPERASI', 'Penerimaan bunga - bersih', 'penerimaan_bunga_deposito', $data),
            $this->row('OPERASI', 'Penerimaan dividen', 'penerimaan_dividen_kas', $data),
            ['OPERASI', 'Penerimaan pendapatan lain-lain', null, ...array_fill(0, count($years), null)],
            ['OPERASI', 'Pencairan instrumen pasar uang - bersih', null, ...array_fill(0, count($years), null)],
            $this->row('OPERASI', 'Hasil penjualan portofolio efek ekuitas', 'penjualan_efek_ekuitas', $data),
            $this->row('OPERASI', 'Pembelian portofolio efek ekuitas', 'pembelian_efek_ekuitas', $data),
            ['OPERASI', 'Penerimaan dari (pengeluaran untuk) piutang lain-lain', null, ...array_fill(0, count($years), null)],
            $this->row('OPERASI', 'Pembayaran beban investasi', 'beban_investasi', $data),
            ['OPERASI', 'Pembayaran pajak penghasilan', null, ...array_fill(0, count($years), null)],
            $this->row('OPERASI', 'Kas Bersih Diperoleh dari (Digunakan untuk) Aktivitas Operasi', 'arus_kas_operasi', $data),
            $this->emptyRow($years),
            ['PENDANAAN', null, null, ...array_fill(0, count($years), null)],
            $this->row('PENDANAAN', 'Penerimaan dari penjualan unit penyertaan', 'penerimaan_penjualan_unit', $data),
            $this->row('PENDANAAN', 'Pembayaran untuk pembelian kembali unit penyertaan', 'pembayaran_pembelian_kembali_unit', $data),
            $this->row('PENDANAAN', 'Kas Bersih Diperoleh dari (Digunakan untuk) Aktivitas Pendanaan', 'arus_kas_pendanaan', $data),
            [null, 'KENAIKAN (PENURUNAN) BERSIH KAS DI BANK', null, ...array_fill(0, count($years), null)],
            $this->row(null, 'KAS DI BANK AWAL TAHUN', 'kas_awal_tahun', $data),
            $this->row(null, 'KAS DI BANK AKHIR TAHUN', 'kas_akhir_tahun', $data),
            $this->emptyRow($years),
            $this->emptyRow($years),
        ];

        $sheet->fromArray($rows, null, 'A1');
        $this->boldHeader($sheet, 4, 3 + count($years));
    }

    private function buildRingkasan(Spreadsheet $spreadsheet, array $data): void
    {
        $sheet = $spreadsheet->createSheet();
        $years = $this->getYears($data);
        $sheet->setTitle('Ringkasan');
        $sheet->getColumnDimension('A')->setWidth(38);
        $sheet->getColumnDimension('B')->setWidth(22);
        $sheet->getColumnDimension('C')->setWidth(22);
        $sheet->getColumnDimension('D')->setWidth(16);
        $sheet->getColumnDimension('E')->setWidth(16);
        $sheet->getColumnDimension('F')->setWidth(16);

        $rows = [
            [null, null, null, null, null, null],
            [null, null, null, null, null, null],
            [null, null, null, null, null, null],
            ['Informasi', 'Keterangan', null, null, null, null],
            ['Sumber file', null, null, null, null, null],
            ['Periode laporan', $this->set($data, 'tahun_laporan'), null, null, null, null],
            ['Satuan', null, null, null, null, null],
            ['Tanggal konversi', $this->set($data, 'tanggal_data'), null, null, null, null],
            ['Catatan', null, null, null, null, null],
            [null, null, null, null, null, null],
            ['Indikator', ...$years, 'Perubahan', '% Perubahan', 'Sumber Sheet'],
            ['Jumlah Aset', ...array_values($this->getYearValues($data, 'total_aset')), null, null, null],
            ['Jumlah Liabilitas', ...array_values($this->getYearValues($data, 'total_liabilitas')), null, null, null],
            ['Nilai Aset Bersih', ...array_values($this->getYearValues($data, 'nilai_aset_bersih')), null, null, null],
            ['Jumlah Unit Penyertaan Beredar', ...array_values($this->getYearValues($data, 'total_unit_beredar')), null, null, null],
            ['NAB per Unit Penyertaan', ...array_values($this->getYearValues($data, 'nab_per_unit')), null, null, null],
            ['Jumlah Pendapatan (Kerugian) - Bersih', ...array_values($this->getYearValues($data, 'total_pendapatan')), null, null, null],
            ['Laba (Rugi) Tahun Berjalan', ...array_values($this->getYearValues($data, 'laba_bersih_tahun_berjalan')), null, null, null],
            ['Kas Bersih dari Aktivitas Operasi', ...array_values($this->getYearValues($data, 'arus_kas_operasi')), null, null, null],
            ['Kas Bersih dari Aktivitas Pendanaan', ...array_values($this->getYearValues($data, 'arus_kas_pendanaan')), null, null, null],
            ['Kas di Bank Akhir Tahun', ...array_values($this->getYearValues($data, 'kas_akhir_tahun')), null, null, null],
        ];

        $sheet->fromArray($rows, null, 'A1');
        $this->boldHeader($sheet, 4, 6);
        $this->boldHeader($sheet, 11, 6);
    }

    private function getYearValues(array $data, string $key): array
    {
        $values = [];
        foreach ($this->getYears($data) as $year) {
            $values[] = $this->getYearValue($data, $key, (string) $year);
        }
        return $values;
    }

    private function buildPerubahanAsetBersih(Spreadsheet $spreadsheet, array $data): void
    {
        $sheet = $spreadsheet->createSheet();
        $years = $this->getYears($data);
        $sheet->setTitle('Perubahan Aset Bersih');
        $sheet->getColumnDimension('A')->setWidth(18);
        $sheet->getColumnDimension('B')->setWidth(48);
        $sheet->getColumnDimension('C')->setWidth(12);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(22);
        $sheet->getColumnDimension('F')->setWidth(20);

        $latestYear = $years[0] ?? 2025;
        $prevYear = $years[1] ?? ($latestYear - 1);

        $rows = [
            [null, null, null, null, null, null],
            [null, null, null, null, null, null],
            [null, null, null, null, null, null],
            ['Periode', 'Uraian', 'Catatan', 'Transaksi dengan Pemegang Unit Penyertaan', 'Kenaikan / (Penurunan) NAB', 'Jumlah Nilai Aset Bersih'],
            ["1 Januari {$prevYear}", 'Saldo pada tanggal 1 Januari ' . $prevYear, null, null, null, null],
            [$prevYear, "Perubahan aset bersih pada tahun {$prevYear}", null, null, null, null],
            [$prevYear, 'Rugi komprehensif tahun berjalan', null, null, $this->set($data, 'penghasilan_komprehensif_tahun_berjalan'), null],
            [$prevYear, 'Transaksi dengan pemegang unit penyertaan', null, null, null, null],
            [$prevYear, 'Penjualan unit penyertaan', null, $this->set($data, 'penerimaan_penjualan_unit'), null, null],
            [$prevYear, 'Pembelian kembali unit penyertaan', null, $this->set($data, 'pembayaran_pembelian_kembali_unit'), null, null],
            [$prevYear, 'Distribusi kepada pemegang unit penyertaan', null, null, null, null],
            ["31 Desember {$prevYear}", 'Saldo pada tanggal 31 Desember ' . $prevYear, null, null, null, null],
            [$latestYear, "Perubahan aset bersih pada tahun {$latestYear}", null, null, null, null],
            [$latestYear, 'Penghasilan komprehensif tahun berjalan', null, null, $this->set($data, 'penghasilan_komprehensif_tahun_berjalan'), null],
            [$latestYear, 'Transaksi dengan pemegang unit penyertaan', null, null, null, null],
            [$latestYear, 'Penjualan unit penyertaan', null, $this->set($data, 'penerimaan_penjualan_unit'), null, null],
            [$latestYear, 'Pembelian kembali unit penyertaan', null, $this->set($data, 'pembayaran_pembelian_kembali_unit'), null, null],
            [$latestYear, 'Distribusi kepada pemegang unit penyertaan', null, null, null, null],
            ["31 Desember {$latestYear}", 'Saldo pada tanggal 31 Desember ' . $latestYear, null, null, null, null],
            [null, null, null, null, null, null],
            [null, null, null, null, null, null],
        ];

        $sheet->fromArray($rows, null, 'A1');
        $this->boldHeader($sheet, 4, 6);
    }
}
