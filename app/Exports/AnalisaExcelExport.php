<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;

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

    private function boldHeader($sheet, int $row, int $maxCol): void
    {
        $sheet->getStyle("A{$row}:" . chr(64 + $maxCol) . "{$row}")
            ->getFont()->setBold(true);
    }

    private function set(array &$data, string $key, $default = null)
    {
        $v = $data[$key] ?? $default;
        return ($v === '' || $v === null) ? $default : $v;
    }

    private function buildPosisiKeuangan(Spreadsheet $spreadsheet, array $data): void
    {
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Posisi Keuangan');
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(50);
        $sheet->getColumnDimension('C')->setWidth(12);
        $sheet->getColumnDimension('D')->setWidth(18);
        $sheet->getColumnDimension('E')->setWidth(18);

        $rows = [
            [null, null, null, null, null],
            [null, null, null, null, null],
            [null, null, null, null, null],
            ['Kategori', 'Uraian', 'Catatan', '2025', '2024'],
            ['ASET', null, null, null, null],
            ['Portofolio efek', 'Efek ekuitas', null, null, null],
            ['Portofolio efek', 'Instrumen pasar uang', null, null, null],
            ['Portofolio efek', 'Jumlah portofolio efek', null, $this->set($data, 'portofolio_efek'), null],
            ['ASET', 'Kas di bank', null, $this->set($data, 'kas_dan_bank'), null],
            ['ASET', 'Piutang transaksi efek', null, $this->set($data, 'piutang_transaksi_efek'), null],
            ['ASET', 'Piutang bunga', null, $this->set($data, 'piutang_bunga'), null],
            ['ASET', 'Piutang dividen', null, $this->set($data, 'piutang_dividen'), null],
            ['ASET', 'Piutang lain-lain', null, $this->set($data, 'piutang_lain'), null],
            ['ASET', 'JUMLAH ASET', null, null, null],
            [null, null, null, null, null],
            ['LIABILITAS', null, null, null, null],
            ['LIABILITAS', 'Pendapatan yang belum didistribusikan', null, null, null],
            ['LIABILITAS', 'Uang muka diterima atas pemesanan unit penyertaan', null, $this->set($data, 'uang_muka_diterima'), null],
            ['LIABILITAS', 'Utang transaksi efek', null, null, null],
            ['LIABILITAS', 'Liabilitas atas pembelian kembali unit penyertaan', null, $this->set($data, 'liabilitas_pembelian_kembali'), null],
            ['LIABILITAS', 'Beban akrual', null, $this->set($data, 'beban_akrual'), null],
            ['LIABILITAS', 'Liabilitas atas biaya pembelian kembali unit penyertaan', null, $this->set($data, 'liabilitas_atas_biaya'), null],
            ['LIABILITAS', 'Utang pajak', null, $this->set($data, 'utang_pajak'), null],
            ['LIABILITAS', 'Utang lain-lain', null, $this->set($data, 'utang_lain'), null],
            ['LIABILITAS', 'JUMLAH LIABILITAS', null, null, null],
            [null, 'NILAI ASET BERSIH', null, $this->set($data, 'nilai_aset_bersih'), null],
            [null, null, null, null, null],
            ['UNIT', 'JUMLAH UNIT PENYERTAAN BEREDAR', null, $this->set($data, 'total_unit_beredar'), null],
            ['UNIT', 'NILAI ASET BERSIH PER UNIT PENYERTAAN', null, $this->set($data, 'nab_per_unit'), null],
            [null, null, null, null, null],
            [null, null, null, null, null],
        ];

        $sheet->fromArray($rows, null, 'A1');
        $this->boldHeader($sheet, 4, 5);
    }

    private function buildLabaRugi(Spreadsheet $spreadsheet, array $data): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Laba Rugi');
        $sheet->getColumnDimension('A')->setWidth(22);
        $sheet->getColumnDimension('B')->setWidth(50);
        $sheet->getColumnDimension('C')->setWidth(12);
        $sheet->getColumnDimension('D')->setWidth(18);
        $sheet->getColumnDimension('E')->setWidth(18);

        $rows = [
            [null, null, null, null, null],
            [null, null, null, null, null],
            [null, null, null, null, null],
            ['Kategori', 'Uraian', 'Catatan', '2025', '2024'],
            ['PENDAPATAN', null, null, null, null],
            ['Pendapatan Investasi', 'Pendapatan bunga', null, $this->set($data, 'pendapatan_bunga'), null],
            ['Pendapatan Investasi', 'Pendapatan dividen', null, $this->set($data, 'pendapatan_dividen'), null],
            ['Pendapatan Investasi', 'Kerugian investasi yang telah direalisasi', null, $this->set($data, 'gain_realized'), null],
            ['Pendapatan Investasi', 'Keuntungan (kerugian) investasi yang belum direalisasi', null, $this->set($data, 'gain_unrealized'), null],
            ['Pendapatan Investasi', 'Pendapatan lain-lain', null, $this->set($data, 'pendapatan_lainnya'), null],
            ['PENDAPATAN', 'JUMLAH PENDAPATAN (KERUGIAN) - BERSIH', null, $this->set($data, 'total_pendapatan'), null],
            [null, null, null, null, null],
            ['BEBAN', null, null, null, null],
            ['Beban Investasi', 'Beban pengelolaan investasi', null, $this->set($data, 'beban_pengelolaan_investasi'), null],
            ['Beban Investasi', 'Beban kustodian', null, $this->set($data, 'beban_kustodian'), null],
            ['Beban Investasi', 'Beban lain-lain', null, $this->set($data, 'beban_lain'), null],
            ['BEBAN', 'JUMLAH BEBAN', null, $this->set($data, 'total_beban'), null],
            [null, 'LABA (RUGI) SEBELUM PAJAK', null, $this->set($data, 'laba_sebelum_pajak'), null],
            [null, 'BEBAN PAJAK', null, $this->set($data, 'beban_pajak_penghasilan'), null],
            [null, 'LABA (RUGI) TAHUN BERJALAN', null, $this->set($data, 'laba_bersih_tahun_berjalan'), null],
            [null, 'PENGHASILAN KOMPREHENSIF LAIN', null, $this->set($data, 'penghasilan_komprehensif_lain'), null],
            [null, 'JUMLAH PENGHASILAN (RUGI) KOMPREHENSIF TAHUN BERJALAN', null, $this->set($data, 'penghasilan_komprehensif_tahun_berjalan'), null],
            [null, null, null, null, null],
            [null, null, null, null, null],
        ];

        $sheet->fromArray($rows, null, 'A1');
        $this->boldHeader($sheet, 4, 5);
    }

    private function buildArusKas(Spreadsheet $spreadsheet, array $data): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Arus Kas');
        $sheet->getColumnDimension('A')->setWidth(12);
        $sheet->getColumnDimension('B')->setWidth(55);
        $sheet->getColumnDimension('C')->setWidth(12);
        $sheet->getColumnDimension('D')->setWidth(18);
        $sheet->getColumnDimension('E')->setWidth(18);

        $rows = [
            [null, null, null, null, null],
            [null, null, null, null, null],
            [null, null, null, null, null],
            ['Kategori', 'Uraian', 'Catatan', '2025', '2024'],
            ['OPERASI', null, null, null, null],
            ['OPERASI', 'Penerimaan bunga - bersih', null, $this->set($data, 'penerimaan_bunga_deposito'), null],
            ['OPERASI', 'Penerimaan dividen', null, $this->set($data, 'penerimaan_dividen_kas'), null],
            ['OPERASI', 'Penerimaan pendapatan lain-lain', null, null, null],
            ['OPERASI', 'Pencairan instrumen pasar uang - bersih', null, null, null],
            ['OPERASI', 'Hasil penjualan portofolio efek ekuitas', null, $this->set($data, 'penjualan_efek_ekuitas'), null],
            ['OPERASI', 'Pembelian portofolio efek ekuitas', null, $this->set($data, 'pembelian_efek_ekuitas'), null],
            ['OPERASI', 'Penerimaan dari (pengeluaran untuk) piutang lain-lain', null, null, null],
            ['OPERASI', 'Pembayaran beban investasi', null, $this->set($data, 'beban_investasi'), null],
            ['OPERASI', 'Pembayaran pajak penghasilan', null, null, null],
            ['OPERASI', 'Kas Bersih Diperoleh dari (Digunakan untuk) Aktivitas Operasi', null, $this->set($data, 'arus_kas_operasi'), null],
            [null, null, null, null, null],
            ['PENDANAAN', null, null, null, null],
            ['PENDANAAN', 'Penerimaan dari penjualan unit penyertaan', null, $this->set($data, 'penerimaan_penjualan_unit'), null],
            ['PENDANAAN', 'Pembayaran untuk pembelian kembali unit penyertaan', null, $this->set($data, 'pembayaran_pembelian_kembali_unit'), null],
            ['PENDANAAN', 'Kas Bersih Diperoleh dari (Digunakan untuk) Aktivitas Pendanaan', null, $this->set($data, 'arus_kas_pendanaan'), null],
            [null, 'KENAIKAN (PENURUNAN) BERSIH KAS DI BANK', null, null, null],
            [null, 'KAS DI BANK AWAL TAHUN', null, $this->set($data, 'kas_awal_tahun'), null],
            [null, 'KAS DI BANK AKHIR TAHUN', null, $this->set($data, 'kas_akhir_tahun'), null],
            [null, null, null, null, null],
            [null, null, null, null, null],
        ];

        $sheet->fromArray($rows, null, 'A1');
        $this->boldHeader($sheet, 4, 5);
    }

    private function buildRingkasan(Spreadsheet $spreadsheet, array $data): void
    {
        $sheet = $spreadsheet->createSheet();
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
            ['Indikator', '2025', '2024', 'Perubahan', '% Perubahan', 'Sumber Sheet'],
            ['Jumlah Aset', $this->set($data, 'total_aset'), null, null, null, null],
            ['Jumlah Liabilitas', $this->set($data, 'total_liabilitas'), null, null, null, null],
            ['Nilai Aset Bersih', $this->set($data, 'nilai_aset_bersih'), null, null, null, null],
            ['Jumlah Unit Penyertaan Beredar', $this->set($data, 'total_unit_beredar'), null, null, null, null],
            ['NAB per Unit Penyertaan', $this->set($data, 'nab_per_unit'), null, null, null, null],
            ['Jumlah Pendapatan (Kerugian) - Bersih', $this->set($data, 'total_pendapatan'), null, null, null, null],
            ['Laba (Rugi) Tahun Berjalan', $this->set($data, 'laba_bersih_tahun_berjalan'), null, null, null, null],
            ['Kas Bersih dari Aktivitas Operasi', $this->set($data, 'arus_kas_operasi'), null, null, null, null],
            ['Kas Bersih dari Aktivitas Pendanaan', $this->set($data, 'arus_kas_pendanaan'), null, null, null, null],
            ['Kas di Bank Akhir Tahun', $this->set($data, 'kas_akhir_tahun'), null, null, null, null],
        ];

        $sheet->fromArray($rows, null, 'A1');
        $this->boldHeader($sheet, 4, 6);
        $this->boldHeader($sheet, 11, 6);
    }

    private function buildPerubahanAsetBersih(Spreadsheet $spreadsheet, array $data): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Perubahan Aset Bersih');
        $sheet->getColumnDimension('A')->setWidth(18);
        $sheet->getColumnDimension('B')->setWidth(48);
        $sheet->getColumnDimension('C')->setWidth(12);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(22);
        $sheet->getColumnDimension('F')->setWidth(20);

        $rows = [
            [null, null, null, null, null, null],
            [null, null, null, null, null, null],
            [null, null, null, null, null, null],
            ['Periode', 'Uraian', 'Catatan', 'Transaksi dengan Pemegang Unit Penyertaan', 'Kenaikan / (Penurunan) NAB', 'Jumlah Nilai Aset Bersih'],
            ['1 Januari 2024', 'Saldo pada tanggal 1 Januari 2024', null, null, null, null],
            ['2024', 'Perubahan aset bersih pada tahun 2024', null, null, null, null],
            ['2024', 'Rugi komprehensif tahun berjalan', null, null, $this->set($data, 'penghasilan_komprehensif_tahun_berjalan'), null],
            ['2024', 'Transaksi dengan pemegang unit penyertaan', null, null, null, null],
            ['2024', 'Penjualan unit penyertaan', null, $this->set($data, 'penerimaan_penjualan_unit'), null, null],
            ['2024', 'Pembelian kembali unit penyertaan', null, $this->set($data, 'pembayaran_pembelian_kembali_unit'), null, null],
            ['2024', 'Distribusi kepada pemegang unit penyertaan', null, null, null, null],
            ['31 Desember 2024', 'Saldo pada tanggal 31 Desember 2024', null, null, null, null],
            ['2025', 'Perubahan aset bersih pada tahun 2025', null, null, null, null],
            ['2025', 'Penghasilan komprehensif tahun berjalan', null, null, $this->set($data, 'penghasilan_komprehensif_tahun_berjalan'), null],
            ['2025', 'Transaksi dengan pemegang unit penyertaan', null, null, null, null],
            ['2025', 'Penjualan unit penyertaan', null, $this->set($data, 'penerimaan_penjualan_unit'), null, null],
            ['2025', 'Pembelian kembali unit penyertaan', null, $this->set($data, 'pembayaran_pembelian_kembali_unit'), null, null],
            ['2025', 'Distribusi kepada pemegang unit penyertaan', null, null, null, null],
            ['31 Desember 2025', 'Saldo pada tanggal 31 Desember 2025', null, null, null, null],
            [null, null, null, null, null, null],
            [null, null, null, null, null, null],
        ];

        $sheet->fromArray($rows, null, 'A1');
        $this->boldHeader($sheet, 4, 6);
    }
}
