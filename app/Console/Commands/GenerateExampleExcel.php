<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class GenerateExampleExcel extends Command
{
    protected $signature = 'example:generate-excel';
    protected $description = 'Generate example Excel file for Analisa Reksa Dana testing';

    public function handle()
    {
        $spreadsheet = new Spreadsheet();

        $this->buildPosisiKeuangan($spreadsheet);
        $this->buildLabaRugi($spreadsheet);
        $this->buildArusKas($spreadsheet);
        $this->buildRingkasan($spreadsheet);
        $this->buildSektor($spreadsheet);
        $this->buildEfek($spreadsheet);
        $this->buildKinerja($spreadsheet);
        $this->buildObligasi($spreadsheet);
        $this->buildSukuk($spreadsheet);
        $this->buildBank($spreadsheet);

        $outputDir = public_path('storage/example');
        if (!is_dir($outputDir)) mkdir($outputDir, 0755, true);
        $path = $outputDir . '/example-full-test.xlsx';

        $writer = new Xlsx($spreadsheet);
        $writer->save($path);

        $this->info("✅ Example Excel created: {$path}");
        $this->warn('Run "php artisan example:test-import" to validate.');
    }

    private function styleHeader($sheet, int $row, int $maxCol): void
    {
        $sheet->getStyle("A{$row}:" . chr(64 + $maxCol) . "{$row}")
            ->getFont()->setBold(true)->setSize(10);
        $sheet->getStyle("A{$row}:" . chr(64 + $maxCol) . "{$row}")
            ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF0F0F0');
    }

    private function writeRow($sheet, int $row, array $data): void
    {
        $sheet->fromArray($data, null, "A{$row}");
    }

    // ─── POSISI KEUANGAN ───────────────────────────────────────────
    private function buildPosisiKeuangan(Spreadsheet $sp): void
    {
        $sheet = $sp->getActiveSheet();
        $sheet->setTitle('Posisi Keuangan');
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(50);
        $sheet->getColumnDimension('C')->setWidth(12);
        foreach (['D','E'] as $c) $sheet->getColumnDimension($c)->setWidth(22);

        $this->writeRow($sheet, 1, [null, null, null, null, null]);
        $this->writeRow($sheet, 2, [null, null, null, null, null]);
        $this->writeRow($sheet, 3, [null, null, null, null, null]);
        $this->writeRow($sheet, 4, ['Kategori', 'Uraian', 'Catatan', 2025, 2024]);
        $this->styleHeader($sheet, 4, 5);
        $this->writeRow($sheet, 5, ['ASET', null, null, null, null]);
        $this->writeRow($sheet, 6, ['Portofolio efek', 'Efek ekuitas', null, null, null]);
        $this->writeRow($sheet, 7, ['Portofolio efek', 'Instrumen pasar uang', null, 30050000000, 28000000000]);
        $this->writeRow($sheet, 8, ['Portofolio efek', 'Jumlah portofolio efek', null, 1863354931760, 1720000000000]);
        $this->writeRow($sheet, 9, ['ASET', 'Kas di bank', null, 389206891, 312000000]);
        $this->writeRow($sheet, 10, ['ASET', 'Piutang transaksi efek', null, 80611427494, 65000000000]);
        $this->writeRow($sheet, 11, ['ASET', 'Piutang bunga', null, 2537882, 2100000]);
        $this->writeRow($sheet, 12, ['ASET', 'Piutang dividen', null, 8614065726, 7200000000]);
        $this->writeRow($sheet, 13, ['ASET', 'Piutang lain-lain', null, 5800, 5000]);
        $this->writeRow($sheet, 14, ['ASET', 'JUMLAH ASET', null, 1952972175553, 1800000000000]);
        $this->writeRow($sheet, 15, [null, null, null, null, null]);
        $this->writeRow($sheet, 16, ['LIABILITAS', null, null, null, null]);
        $this->writeRow($sheet, 17, ['LIABILITAS', 'Pendapatan yang belum didistribusikan', null, null, null]);
        $this->writeRow($sheet, 18, ['LIABILITAS', 'Uang muka diterima atas pemesanan unit penyertaan', null, 29260209, 25000000]);
        $this->writeRow($sheet, 19, ['LIABILITAS', 'Utang transaksi efek', null, null, null]);
        $this->writeRow($sheet, 20, ['LIABILITAS', 'Liabilitas atas pembelian kembali unit penyertaan', null, 932768752, 800000000]);
        $this->writeRow($sheet, 21, ['LIABILITAS', 'Beban akrual', null, 4581630646, 4000000000]);
        $this->writeRow($sheet, 22, ['LIABILITAS', 'Liabilitas atas biaya pembelian kembali unit penyertaan', null, 3260973423, 3000000000]);
        $this->writeRow($sheet, 23, ['LIABILITAS', 'Utang pajak', null, '-', '-']);
        $this->writeRow($sheet, 24, ['LIABILITAS', 'Utang lain-lain', null, 85193098, 75000000]);
        $this->writeRow($sheet, 25, ['LIABILITAS', 'JUMLAH LIABILITAS', null, 25933045900, 22000000000]);
        $this->writeRow($sheet, 26, [null, 'NILAI ASET BERSIH', null, 1927039129653, 1778000000000]);
        $this->writeRow($sheet, 27, [null, null, null, null, null]);
        $this->writeRow($sheet, 28, ['UNIT', 'JUMLAH UNIT PENYERTAAN BEREDAR', null, 1489989787.5455, 1400000000]);
        $this->writeRow($sheet, 29, ['UNIT', 'NILAI ASET BERSIH PER UNIT PENYERTAAN', null, 1293.32, 1270.00]);
    }

    // ─── LABA RUGI ───────────────────────────────────────────
    private function buildLabaRugi(Spreadsheet $sp): void
    {
        $sheet = $sp->createSheet();
        $sheet->setTitle('Laba Rugi');
        $sheet->getColumnDimension('A')->setWidth(22);
        $sheet->getColumnDimension('B')->setWidth(52);
        $sheet->getColumnDimension('C')->setWidth(12);
        foreach (['D','E'] as $c) $sheet->getColumnDimension($c)->setWidth(22);

        $this->writeRow($sheet, 1, [null, null, null, null, null]);
        $this->writeRow($sheet, 2, [null, null, null, null, null]);
        $this->writeRow($sheet, 3, [null, null, null, null, null]);
        $this->writeRow($sheet, 4, ['Kategori', 'Uraian', 'Catatan', 2025, 2024]);
        $this->styleHeader($sheet, 4, 5);
        $this->writeRow($sheet, 5, ['PENDAPATAN', null, null, null, null]);
        $this->writeRow($sheet, 6, ['Pendapatan Investasi', 'Pendapatan bunga', null, 85000000000, 78000000000]);
        $this->writeRow($sheet, 7, ['Pendapatan Investasi', 'Pendapatan dividen', null, 42000000000, 38000000000]);
        $this->writeRow($sheet, 8, ['Pendapatan Investasi', 'Kerugian investasi yang telah direalisasi', null, -5200000000, -4800000000]);
        $this->writeRow($sheet, 9, ['Pendapatan Investasi', 'Keuntungan (kerugian) investasi yang belum direalisasi', null, 12500000000, -3200000000]);
        $this->writeRow($sheet, 10, ['Pendapatan Investasi', 'Pendapatan lain-lain', null, 2500000000, 2200000000]);
        $this->writeRow($sheet, 11, ['PENDAPATAN', 'JUMLAH PENDAPATAN (KERUGIAN) - BERSIH', null, 140300000000, 110200000000]);
        $this->writeRow($sheet, 12, [null, null, null, null, null]);
        $this->writeRow($sheet, 13, ['BEBAN', null, null, null, null]);
        $this->writeRow($sheet, 14, ['Beban Investasi', 'Beban pengelolaan investasi', null, 18500000000, 17200000000]);
        $this->writeRow($sheet, 15, ['Beban Investasi', 'Beban kustodian', null, 3200000000, 2900000000]);
        $this->writeRow($sheet, 16, ['Beban Investasi', 'Beban lain-lain', null, 1500000000, 1400000000]);
        $this->writeRow($sheet, 17, ['BEBAN', 'JUMLAH BEBAN', null, 23200000000, 21500000000]);
        $this->writeRow($sheet, 18, [null, 'LABA (RUGI) SEBELUM PAJAK', null, 117100000000, 88700000000]);
        $this->writeRow($sheet, 19, [null, 'BEBAN PAJAK', null, 29000000000, 22000000000]);
        $this->writeRow($sheet, 20, [null, 'LABA (RUGI) TAHUN BERJALAN', null, 88100000000, 66700000000]);
        $this->writeRow($sheet, 21, [null, 'LABA BERSIH', null, 88100000000, 66700000000]);
        $this->writeRow($sheet, 22, [null, 'PENGHASILAN KOMPREHENSIF LAIN', null, 500000000, 300000000]);
        $this->writeRow($sheet, 23, [null, 'JUMLAH PENGHASILAN (RUGI) KOMPREHENSIF TAHUN BERJALAN', null, 88600000000, 67000000000]);
    }

    // ─── ARUS KAS ───────────────────────────────────────────
    private function buildArusKas(Spreadsheet $sp): void
    {
        $sheet = $sp->createSheet();
        $sheet->setTitle('Arus Kas');
        $sheet->getColumnDimension('A')->setWidth(14);
        $sheet->getColumnDimension('B')->setWidth(58);
        $sheet->getColumnDimension('C')->setWidth(12);
        foreach (['D','E'] as $c) $sheet->getColumnDimension($c)->setWidth(22);

        $this->writeRow($sheet, 1, [null, null, null, null, null]);
        $this->writeRow($sheet, 2, [null, null, null, null, null]);
        $this->writeRow($sheet, 3, [null, null, null, null, null]);
        $this->writeRow($sheet, 4, ['Kategori', 'Uraian', 'Catatan', 2025, 2024]);
        $this->styleHeader($sheet, 4, 5);
        $this->writeRow($sheet, 5, ['OPERASI', null, null, null, null]);
        $this->writeRow($sheet, 6, ['OPERASI', 'Penerimaan bunga - bersih', null, 82000000000, 75000000000]);
        $this->writeRow($sheet, 7, ['OPERASI', 'Penerimaan dividen', null, 40000000000, 36000000000]);
    
        $this->writeRow($sheet, 8, ['OPERASI', 'Penerimaan pendapatan lain-lain', null, null, null]);
        $this->writeRow($sheet, 9, ['OPERASI', 'Pencairan instrumen pasar uang - bersih', null, null, null]);
        $this->writeRow($sheet, 10, ['OPERASI', 'Hasil penjualan portofolio efek ekuitas', null, 250000000000, 200000000000]);
        $this->writeRow($sheet, 11, ['OPERASI', 'Pembelian portofolio efek ekuitas', null, -300000000000, -250000000000]);
        $this->writeRow($sheet, 12, ['OPERASI', 'Penerimaan dari (pengeluaran untuk) piutang lain-lain', null, null, null]);
        $this->writeRow($sheet, 13, ['OPERASI', 'Pembayaran beban investasi', null, -22000000000, -20000000000]);
        $this->writeRow($sheet, 14, ['OPERASI', 'Pembayaran pajak penghasilan', null, null, null]);
        $this->writeRow($sheet, 15, ['OPERASI', 'Kas Bersih Diperoleh dari (Digunakan untuk) Aktivitas Operasi', null, 26800000000, 31000000000]);
        $this->writeRow($sheet, 16, [null, null, null, null, null]);
        $this->writeRow($sheet, 17, ['PENDANAAN', null, null, null, null]);
        $this->writeRow($sheet, 18, ['PENDANAAN', 'Penerimaan dari penjualan unit penyertaan', null, 50000000000, 45000000000]);
        $this->writeRow($sheet, 19, ['PENDANAAN', 'Pembayaran untuk pembelian kembali unit penyertaan', null, -35000000000, -30000000000]);
        $this->writeRow($sheet, 20, ['PENDANAAN', 'Kas Bersih Diperoleh dari (Digunakan untuk) Aktivitas Pendanaan', null, 15000000000, 15000000000]);
        $this->writeRow($sheet, 21, [null, 'KENAIKAN (PENURUNAN) BERSIH KAS DI BANK', null, null, null]);
        $this->writeRow($sheet, 22, [null, 'KAS DI BANK AWAL TAHUN', null, 312000000, 280000000]);
        $this->writeRow($sheet, 23, [null, 'KAS DI BANK AKHIR TAHUN', null, 389206891, 312000000]);
    }

    // ─── RINGKASAN ───────────────────────────────────────────
    private function buildRingkasan(Spreadsheet $sp): void
    {
        $sheet = $sp->createSheet();
        $sheet->setTitle('Ringkasan');
        $sheet->getColumnDimension('A')->setWidth(40);
        $sheet->getColumnDimension('B')->setWidth(22);
        foreach (['C','D','E','F'] as $c) $sheet->getColumnDimension($c)->setWidth(16);

        $this->writeRow($sheet, 1, [null, null, null, null, null, null]);
        $this->writeRow($sheet, 2, [null, null, null, null, null, null]);
        $this->writeRow($sheet, 3, [null, null, null, null, null, null]);
        $this->writeRow($sheet, 4, ['Informasi', 'Keterangan', null, null, null, null]);
        $this->writeRow($sheet, 5, ['Sumber file', null, null, null, null, null]);
        $this->writeRow($sheet, 6, ['Periode laporan', 2025, null, null, null, null]);
        $this->writeRow($sheet, 7, ['Satuan', null, null, null, null, null]);
        $this->writeRow($sheet, 8, ['Tanggal konversi', '30/06/2026', null, null, null, null]);
        $this->writeRow($sheet, 9, ['Catatan', null, null, null, null, null]);
        $this->writeRow($sheet, 10, [null, null, null, null, null, null]);
    }

    // ─── SEKTOR ───────────────────────────────────────────
    private function buildSektor(Spreadsheet $sp): void
    {
        $sheet = $sp->createSheet();
        $sheet->setTitle('Sektor');
        $sheet->getColumnDimension('A')->setWidth(24);
        $sheet->getColumnDimension('B')->setWidth(14);

        $this->writeRow($sheet, 1, ['nama_sektor', 'bobot']);
        $this->styleHeader($sheet, 1, 2);
        $this->writeRow($sheet, 2, ['Keuangan', 25.50]);
        $this->writeRow($sheet, 3, ['Energi', 15.00]);
        $this->writeRow($sheet, 4, ['Teknologi', 12.80]);
        $this->writeRow($sheet, 5, ['Infrastruktur', 10.50]);
        $this->writeRow($sheet, 6, ['Properti', 8.20]);
        $this->writeRow($sheet, 7, ['Konsumsi', 7.50]);
    }

    // ─── EFEK ───────────────────────────────────────────
    private function buildEfek(Spreadsheet $sp): void
    {
        $sheet = $sp->createSheet();
        $sheet->setTitle('Efek');
        foreach (range('A','L') as $c) $sheet->getColumnDimension($c)->setWidth(16);
        $sheet->getColumnDimension('B')->setWidth(28);

        $this->writeRow($sheet, 1, ['kode_efek','nama_efek','sektor','bobot','nilai_pasar','kontribusi_kinerja','return_1m','return_3m','return_6m','return_1y','market_cap','top_10']);
        $this->styleHeader($sheet, 1, 12);
        $this->writeRow($sheet, 2, ['BBCA','Bank Central Asia Tbk','Keuangan',10.50,null,0.35,1.20,3.80,5.50,8.20,950000000000000,'Ya']);
        $this->writeRow($sheet, 3, ['TLKM','Telkom Indonesia Tbk','Teknologi',8.20,null,-0.12,-0.50,1.20,2.80,4.10,280000000000000,'Ya']);
        $this->writeRow($sheet, 4, ['BBRI','Bank Rakyat Indonesia','Keuangan',7.80,null,0.28,0.90,2.50,4.80,7.50,650000000000000,'Ya']);
        $this->writeRow($sheet, 5, ['ASII','Astra International','Konsumsi',5.20,null,-0.05,-0.30,1.80,3.20,5.90,180000000000000,'Tidak']);
        $this->writeRow($sheet, 6, ['PGAS','Perusahaan Gas Negara','Energi',4.50,null,0.15,0.60,2.10,3.50,6.20,85000000000000,'Tidak']);
    }

    // ─── KINERJA ───────────────────────────────────────────
    private function buildKinerja(Spreadsheet $sp): void
    {
        $sheet = $sp->createSheet();
        $sheet->setTitle('Kinerja');
        $sheet->getColumnDimension('A')->setWidth(16);
        $sheet->getColumnDimension('B')->setWidth(14);

        $this->writeRow($sheet, 1, ['periode', 'return_pct']);
        $this->styleHeader($sheet, 1, 2);
        $this->writeRow($sheet, 2, ['2024-01-01', 1.25]);
        $this->writeRow($sheet, 3, ['2024-02-01', -0.50]);
        $this->writeRow($sheet, 4, ['2024-03-01', 2.10]);
        $this->writeRow($sheet, 5, ['2024-04-01', 1.80]);
        $this->writeRow($sheet, 6, ['2024-05-01', -0.30]);
        $this->writeRow($sheet, 7, ['2024-06-01', 0.90]);
        $this->writeRow($sheet, 8, ['2024-07-01', 1.50]);
        $this->writeRow($sheet, 9, ['2024-08-01', -0.80]);
        $this->writeRow($sheet, 10, ['2024-09-01', 2.30]);
        $this->writeRow($sheet, 11, ['2024-10-01', 1.10]);
        $this->writeRow($sheet, 12, ['2024-11-01', 0.40]);
        $this->writeRow($sheet, 13, ['2024-12-01', 1.75]);
    }

    // ─── OBLIGASI ───────────────────────────────────────────
    private function buildObligasi(Spreadsheet $sp): void
    {
        $sheet = $sp->createSheet();
        $sheet->setTitle('Obligasi');
        foreach (range('A','J') as $c) $sheet->getColumnDimension($c)->setWidth(16);
        $sheet->getColumnDimension('B')->setWidth(28);

        $this->writeRow($sheet, 1, ['kode_obligasi','nama_obligasi','bobot','nilai_pasar','return_1m','return_3m','return_6m','return_1y','durasi','rating']);
        $this->styleHeader($sheet, 1, 10);
        $this->writeRow($sheet, 2, ['FR0091','Obligasi Negara FR0091',15.00,null,null,null,null,null,7.50,'AAA']);
        $this->writeRow($sheet, 3, ['BBRI01','Obligasi BRI 2025',8.00,null,null,null,null,null,3.20,'AA+']);
        $this->writeRow($sheet, 4, ['BMRI01','Obligasi Mandiri 2025',6.50,null,null,null,null,null,4.10,'AA+']);
        $this->writeRow($sheet, 5, ['PLN01','Obligasi PLN 2026',4.80,null,null,null,null,null,5.30,'AA']);
    }

    // ─── SUKUK ───────────────────────────────────────────
    private function buildSukuk(Spreadsheet $sp): void
    {
        $sheet = $sp->createSheet();
        $sheet->setTitle('Sukuk');
        foreach (range('A','G') as $c) $sheet->getColumnDimension($c)->setWidth(18);

        $this->writeRow($sheet, 1, ['kode_sukuk','nama_sukuk','jenis_sukuk','bobot','yield','jatuh_tempo','rating']);
        $this->styleHeader($sheet, 1, 7);
        $this->writeRow($sheet, 2, ['SR019','Sukuk Ritel SR019','Negara',15.00,6.25,'2028','AAA']);
        $this->writeRow($sheet, 3, ['SBSN01','SBSN Indonesia','Negara',10.00,5.80,'2029','AAA']);
        $this->writeRow($sheet, 4, ['ISAT01','Sukuk Indosat','Korporasi',5.00,7.10,'2029','AA+']);
    }

    // ─── BANK ───────────────────────────────────────────
    private function buildBank(Spreadsheet $sp): void
    {
        $sheet = $sp->createSheet();
        $sheet->setTitle('Bank');
        foreach (range('A','K') as $c) $sheet->getColumnDimension($c)->setWidth(16);
        $sheet->getColumnDimension('A')->setWidth(20);

        $this->writeRow($sheet, 1, ['nama_bank','jenis_bank','bobot','nilai_pasar','return_1m','return_3m','return_6m','return_1y','car','npl','klasifikasi_risiko']);
        $this->styleHeader($sheet, 1, 11);
        $this->writeRow($sheet, 2, ['Bank BCA','Bank Nasional',20.00,null,null,null,null,null,25.50,1.20,'Rendah']);
        $this->writeRow($sheet, 3, ['Bank Mandiri','Bank Nasional',15.00,null,null,null,null,null,21.30,2.10,'Rendah']);
        $this->writeRow($sheet, 4, ['Bank BNI','Bank Nasional',12.00,null,null,null,null,null,19.80,2.50,'Rendah']);
        $this->writeRow($sheet, 5, ['Bank BRI','Bank Nasional',10.00,null,null,null,null,null,22.40,1.80,'Rendah']);
    }
}
