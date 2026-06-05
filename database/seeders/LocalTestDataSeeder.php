<?php

namespace Database\Seeders;

use App\Models\ReksaDana;
use App\Models\ReksaDanaDocument;
use App\Models\AnalisaReksaDana;
use App\Models\AnalisaSektor;
use App\Models\AnalisaEfek;
use App\Models\AnalisaKinerjaBulanan;
use App\Models\AnalisaObligasi;
use App\Models\AnalisaBank;
use App\Models\AnalisaSukuk;
use App\Models\AnalisaAlokasiAset;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class LocalTestDataSeeder extends Seeder
{
    public function run(): void
    {
        if (!app()->environment('local')) {
            $this->command->warn('LocalTestDataSeeder hanya berjalan di environment local.');
            return;
        }

        $admin = User::where('email', 'admin@example.com')->first();
        if (!$admin) {
            $this->command->warn('Admin user tidak ditemukan. Jalankan DatabaseSeeder dulu.');
            return;
        }

        Storage::disk('public')->makeDirectory('reksa-dana-documents');

        // === REKSA DANA ===
        $funds = [
            [
                'kode_reksa_dana' => 'RD001',
                'nama_reksa_dana' => 'Premier Equity Growth Fund',
                'nama_manajer_investasi' => 'PT Premier Investama',
                'jenis' => 'Saham',
                'kategori' => ['Saham'],
                'kategori_produk' => 'saham',
                'kelas' => 'A',
                'benchmark' => 'JCI',
                'mata_uang' => 'IDR',
                'nab_per_unit' => 2500.000000,
                'tanggal_nab' => now(),
            ],
            [
                'kode_reksa_dana' => 'RD002',
                'nama_reksa_dana' => 'Premier Fixed Income Fund',
                'nama_manajer_investasi' => 'PT Premier Investama',
                'jenis' => 'Pendapatan Tetap',
                'kategori' => ['Pendapatan Tetap'],
                'kategori_produk' => 'pendapatan_tetap',
                'kelas' => 'A',
                'benchmark' => 'Indeks Obligasi Pemerintah',
                'mata_uang' => 'IDR',
                'nab_per_unit' => 1500.000000,
                'tanggal_nab' => now(),
            ],
            [
                'kode_reksa_dana' => 'RD003',
                'nama_reksa_dana' => 'Premier Balanced Fund',
                'nama_manajer_investasi' => 'PT Premier Investama',
                'jenis' => 'Campuran',
                'kategori' => ['Campuran'],
                'kategori_produk' => 'campuran',
                'kelas' => 'A',
                'benchmark' => '50% JCI + 50% Obligasi',
                'mata_uang' => 'IDR',
                'nab_per_unit' => 1800.000000,
                'tanggal_nab' => now(),
            ],
            [
                'kode_reksa_dana' => 'RD004',
                'nama_reksa_dana' => 'Premier Money Market Fund',
                'nama_manajer_investasi' => 'PT Premier Investama',
                'jenis' => 'Pasar Uang',
                'kategori' => ['Pasar Uang'],
                'kategori_produk' => 'pasar_uang',
                'kelas' => 'A',
                'benchmark' => 'Indeks Pasar Uang',
                'mata_uang' => 'IDR',
                'nab_per_unit' => 1100.000000,
                'tanggal_nab' => now(),
            ],
        ];

        $fundModels = [];
        foreach ($funds as $fund) {
            $fundModels[] = ReksaDana::firstOrCreate(
                ['kode_reksa_dana' => $fund['kode_reksa_dana']],
                $fund
            );
        }

        $this->command->info('Created ' . count($fundModels) . ' Reksa Dana.');

        // === GENERATE SAMPLE PDFs ===
        $ffsContents = [
            "FUND FACT SHEET\n" .
            "Nama Reksa Dana: Premier Equity Growth Fund\n" .
            "Jenis Reksa Dana: Saham\n" .
            "Manajer Investasi: PT Premier Investama\n" .
            "Tanggal Data: 2026-04-30\n" .
            "Total AUM: Rp 1,500,000,000,000\n" .
            "NAB/UP: Rp 2,500\n" .
            "Unit Penyertaan: 600,000,000\n" .
            "Return YTD: 3.50%\n" .
            "Return 1 Tahun: 12.75%\n" .
            "Total Return: 8.20%\n" .
            "Biaya Operasi: 1.85%\n" .
            "Portfolio Turnover Ratio: 45.20%\n" .
            "Management Fee: 2.00%\n" .
            "Custodian Fee: 0.25%\n" .
            "Total Aset: Rp 1,520,000,000,000\n" .
            "Total Liabilitas: Rp 20,000,000,000\n" .
            "Kas dan Bank: Rp 50,000,000,000\n" .
            "Piutang Bunga: Rp 5,000,000,000\n" .
            "Piutang Dividen: Rp 3,000,000,000\n" .
            "Piutang Lain-lain: Rp 2,000,000,000\n" .
            "Utang Pajak: Rp 1,500,000,000\n" .
            "Utang Lain-lain: Rp 800,000,000\n" .
            "Pendapatan Bunga: Rp 15,000,000,000\n" .
            "Pendapatan Dividen: Rp 25,000,000,000\n" .
            "Gain Realized: Rp 30,000,000,000\n" .
            "Gain Unrealized: Rp 10,000,000,000\n" .
            "Beban Manajer Investasi: Rp 8,000,000,000\n" .
            "Beban Kustodian: Rp 1,200,000,000\n" .
            "Beban Lain-lain: Rp 500,000,000\n" .
            "Laba Bersih: Rp 70,300,000,000\n" .
            "Arus Kas Operasi: Rp 65,000,000,000\n" .
            "Arus Kas Pendanaan: Rp -10,000,000,000\n" .
            "Kas Awal Tahun: Rp 45,000,000,000\n" .
            "Kas Akhir Tahun: Rp 50,000,000,000\n" .
            "Total Hasil Investasi: 15.50%\n" .
            "Hasil Investasi Setelah Biaya Pemasaran: 13.25%\n" .
            "Persentase Penghasilan Kena Pajak: 22.00%\n" .
            "Fair Value Level 1: Rp 1,200,000,000,000\n" .
            "Fair Value Level 2: Rp 250,000,000,000\n" .
            "Fair Value Level 3: Rp 50,000,000,000\n" .
            "Unit Milik Investor: 580,000,000\n" .
            "Unit Milik Manajer Investasi: 20,000,000\n" .
            "Total Unit Beredar: 600,000,000\n" .
            "\nAlokasi Aset:\n" .
            "Saham: 75.00%\n" .
            "Obligasi: 10.00%\n" .
            "Pasar Uang: 8.00%\n" .
            "Kas: 5.00%\n" .
            "Lainnya: 2.00%\n" .
            "\nTop 10 Efek:\n" .
            "BBCA - PT Bank Central Asia Tbk - 8.50%\n" .
            "BBRI - PT Bank Rakyat Indonesia Tbk - 7.20%\n" .
            "TLKM - PT Telkom Indonesia Tbk - 6.80%\n" .
            "ASII - PT Astra International Tbk - 5.50%\n" .
            "ADRO - PT Adaro Energy Tbk - 4.90%\n",

            "FUND FACT SHEET\n" .
            "Nama Reksa Dana: Premier Fixed Income Fund\n" .
            "Jenis Reksa Dana: Pendapatan Tetap\n" .
            "Manajer Investasi: PT Premier Investama\n" .
            "Tanggal Data: 2026-04-30\n" .
            "Total AUM: Rp 2,000,000,000,000\n" .
            "NAB/UP: Rp 1,500\n" .
            "Unit Penyertaan: 1,333,333,333\n" .
            "Return YTD: 2.80%\n" .
            "Return 1 Tahun: 8.50%\n" .
            "Total Return: 5.60%\n" .
            "Biaya Operasi: 1.20%\n" .
            "Portfolio Turnover Ratio: 35.00%\n" .
            "Management Fee: 1.50%\n" .
            "Custodian Fee: 0.20%\n" .
            "Total Aset: Rp 2,050,000,000,000\n" .
            "Total Liabilitas: Rp 50,000,000,000\n" .
            "Kas dan Bank: Rp 30,000,000,000\n" .
            "Piutang Bunga: Rp 25,000,000,000\n" .
            "Utang Pajak: Rp 2,000,000,000\n" .
            "Pendapatan Bunga: Rp 85,000,000,000\n" .
            "Beban Manajer Investasi: Rp 5,000,000,000\n" .
            "Beban Kustodian: Rp 800,000,000\n" .
            "Laba Bersih: Rp 79,200,000,000\n" .
            "Arus Kas Operasi: Rp 72,000,000,000\n" .
            "Total Hasil Investasi: 9.80%\n" .
            "Fair Value Level 1: Rp 1,800,000,000,000\n" .
            "Fair Value Level 2: Rp 200,000,000,000\n" .
            "Unit Milik Investor: 1,300,000,000\n" .
            "Total Unit Beredar: 1,333,333,333\n" .
            "\nAlokasi Aset:\n" .
            "Obligasi Pemerintah: 60.00%\n" .
            "Obligasi Korporasi: 25.00%\n" .
            "Pasar Uang: 10.00%\n" .
            "Kas: 5.00%\n" .
            "\nObligasi:\n" .
            "FR0090 - Obligasi Pemerintah RI - 15.00% - 5.20% - AAA\n" .
            "FR0085 - Obligasi Pemerintah RI - 12.00% - 5.50% - AAA\n" .
            "FR0095 - Obligasi Pemerintah RI - 10.00% - 4.80% - AAA\n",

            "FUND FACT SHEET\n" .
            "Nama Reksa Dana: Premier Balanced Fund\n" .
            "Jenis Reksa Dana: Campuran\n" .
            "Manajer Investasi: PT Premier Investama\n" .
            "Tanggal Data: 2026-04-30\n" .
            "Total AUM: Rp 800,000,000,000\n" .
            "NAB/UP: Rp 1,800\n" .
            "Unit Penyertaan: 444,444,444\n" .
            "Return YTD: 2.10%\n" .
            "Return 1 Tahun: 9.30%\n" .
            "Total Return: 6.80%\n" .
            "Biaya Operasi: 1.60%\n" .
            "Management Fee: 1.80%\n" .
            "Custodian Fee: 0.22%\n" .
            "Total Aset: Rp 820,000,000,000\n" .
            "Total Liabilitas: Rp 20,000,000,000\n" .
            "Kas dan Bank: Rp 25,000,000,000\n" .
            "Piutang Bunga: Rp 8,000,000,000\n" .
            "Piutang Dividen: Rp 5,000,000,000\n" .
            "Utang Pajak: Rp 1,000,000,000\n" .
            "Pendapatan Bunga: Rp 12,000,000,000\n" .
            "Pendapatan Dividen: Rp 18,000,000,000\n" .
            "Gain Realized: Rp 15,000,000,000\n" .
            "Gain Unrealized: Rp 5,000,000,000\n" .
            "Beban Manajer Investasi: Rp 4,000,000,000\n" .
            "Beban Kustodian: Rp 600,000,000\n" .
            "Laba Bersih: Rp 45,400,000,000\n" .
            "Arus Kas Operasi: Rp 40,000,000,000\n" .
            "Total Hasil Investasi: 11.20%\n" .
            "Hasil Investasi Setelah Biaya Pemasaran: 9.40%\n" .
            "Fair Value Level 1: Rp 600,000,000,000\n" .
            "Fair Value Level 2: Rp 180,000,000,000\n" .
            "Unit Milik Investor: 430,000,000\n" .
            "Unit Milik Manajer Investasi: 14,444,444\n" .
            "Total Unit Beredar: 444,444,444\n" .
            "\nAlokasi Aset:\n" .
            "Saham: 50.00%\n" .
            "Obligasi: 30.00%\n" .
            "Pasar Uang: 12.00%\n" .
            "Kas: 8.00%\n" .
            "\nTop 10 Efek:\n" .
            "BBCA - PT Bank Central Asia Tbk - 6.50%\n" .
            "BBRI - PT Bank Rakyat Indonesia Tbk - 5.20%\n" .
            "TLKM - PT Telkom Indonesia Tbk - 4.80%\n" .
            "FR0090 - Obligasi Pemerintah RI - 8.00%\n",
        ];

        $documents = [];
        $months = [4, 5, 6];
        foreach ($fundModels as $i => $fund) {
            foreach ($months as $month) {
                $content = $ffsContents[$i % count($ffsContents)];
                $filename = 'ffs-' . strtolower(str_replace(' ', '-', $fund->nama_reksa_dana)) . "-2026-{$month}.pdf";
                $filePath = 'reksa-dana-documents/' . $filename;
                Storage::disk('public')->put($filePath, $this->makePdf($content));

                $documents[] = ReksaDanaDocument::create([
                    'reksa_dana_id' => $fund->id,
                    'uploaded_by' => $admin->id,
                    'document_type' => ReksaDanaDocument::TYPE_FFS,
                    'ffs_month' => $month,
                    'ffs_year' => 2026,
                    'original_name' => 'FFS ' . $fund->nama_reksa_dana . ' ' . date('M', mktime(0, 0, 0, $month, 1)) . ' 2026.pdf',
                    'file_path' => $filePath,
                    'mime_type' => 'application/pdf',
                    'file_size' => Storage::disk('public')->size($filePath),
                ]);
            }
        }

        $this->command->info('Created ' . count($documents) . ' documents with sample PDFs.');

        // === SAMPLE DRAFTS ===
        $draft = AnalisaReksaDana::create([
            'user_id' => $admin->id,
            'product_type' => 'reksa_dana',
            'kode_reksa_dana' => 'RD001',
            'nama_reksa_dana' => 'Premier Equity Growth Fund',
            'jenis_reksa_dana' => 'Saham',
            'kategori' => ['Saham'],
            'manajer_investasi' => 'PT Premier Investama',
            'bank_kustodian' => 'Bank Mandiri',
            'mata_uang' => 'IDR',
            'total_aum' => 1500000000000,
            'nab_per_unit' => 2500.000000,
            'unit_penyertaan' => 600000000,
            'ffs_bulan' => 4,
            'ffs_tahun' => 2026,
            'tanggal_data' => '2026-04-30',
            'total_aset' => 1520000000000,
            'total_liabilitas' => 20000000000,
            'kas_dan_bank' => 50000000000,
            'piutang_bunga' => 5000000000,
            'piutang_dividen' => 3000000000,
            'piutang_lain' => 2000000000,
            'utang_pajak' => 1500000000,
            'utang_lain' => 800000000,
            'pendapatan_bunga' => 15000000000,
            'pendapatan_dividen' => 25000000000,
            'gain_realized' => 30000000000,
            'gain_unrealized' => 10000000000,
            'beban_mi' => 8000000000,
            'beban_kustodian' => 1200000000,
            'beban_lain' => 500000000,
            'laba_bersih' => 70300000000,
            'arus_kas_operasi' => 65000000000,
            'arus_kas_pendanaan' => -10000000000,
            'kas_awal_tahun' => 45000000000,
            'kas_akhir_tahun' => 50000000000,
            'return_ytd' => 3.50,
            'return_1y' => 12.75,
            'total_return' => 8.20,
            'biaya_operasi' => 1.85,
            'portfolio_turnover_ratio' => 45.20,
            'total_hasil_investasi' => 15.50,
            'hasil_investasi_setelah_biaya' => 13.25,
            'persentase_pph' => 22.00,
            'fair_value_level_1' => 1200000000000,
            'fair_value_level_2' => 250000000000,
            'fair_value_level_3' => 50000000000,
            'unit_milik_investor' => 580000000,
            'unit_milik_mi' => 20000000,
            'total_unit_beredar' => 600000000,
            'status' => 'input_manual',
            'mode' => 'lengkap',
        ]);

        // Sektor
        $sektors = [
            ['nama_sektor' => 'Perbankan', 'bobot' => 35.50],
            ['nama_sektor' => 'Telekomunikasi', 'bobot' => 15.20],
            ['nama_sektor' => 'Infrastruktur', 'bobot' => 12.80],
            ['nama_sektor' => 'Konsumsi', 'bobot' => 10.50],
            ['nama_sektor' => 'Energi', 'bobot' => 8.00],
        ];
        foreach ($sektors as $s) {
            AnalisaSektor::create(array_merge($s, ['analisa_reksa_dana_id' => $draft->id]));
        }

        // Efek
        $efeks = [
            ['kode_efek' => 'BBCA', 'nama_efek' => 'PT Bank Central Asia Tbk', 'sektor' => 'Perbankan', 'bobot' => 8.50, 'market_cap' => 1000000000000, 'top_10' => true],
            ['kode_efek' => 'BBRI', 'nama_efek' => 'PT Bank Rakyat Indonesia Tbk', 'sektor' => 'Perbankan', 'bobot' => 7.20, 'market_cap' => 800000000000, 'top_10' => true],
            ['kode_efek' => 'TLKM', 'nama_efek' => 'PT Telkom Indonesia Tbk', 'sektor' => 'Telekomunikasi', 'bobot' => 6.80, 'market_cap' => 600000000000, 'top_10' => true],
            ['kode_efek' => 'ASII', 'nama_efek' => 'PT Astra International Tbk', 'sektor' => 'Konsumsi', 'bobot' => 5.50, 'market_cap' => 500000000000, 'top_10' => true],
            ['kode_efek' => 'ADRO', 'nama_efek' => 'PT Adaro Energy Tbk', 'sektor' => 'Energi', 'bobot' => 4.90, 'market_cap' => 300000000000, 'top_10' => true],
        ];
        foreach ($efeks as $e) {
            AnalisaEfek::create(array_merge($e, ['analisa_reksa_dana_id' => $draft->id]));
        }

        // Kinerja
        $kinerja = [];
        for ($m = 1; $m <= 12; $m++) {
            $kinerja[] = ['periode' => "2026-{$m}-01", 'return_pct' => round(rand(-300, 500) / 100, 2)];
        }
        foreach ($kinerja as $k) {
            AnalisaKinerjaBulanan::create(array_merge($k, ['analisa_reksa_dana_id' => $draft->id]));
        }

        // Obligasi
        $obligasis = [
            ['kode_obligasi' => 'FR0090', 'nama_obligasi' => 'Obligasi Pemerintah RI FR0090', 'bobot' => 5.00, 'durasi' => 5.2, 'rating' => 'AAA', 'ytm' => 5.20],
            ['kode_obligasi' => 'FR0085', 'nama_obligasi' => 'Obligasi Pemerintah RI FR0085', 'bobot' => 3.00, 'durasi' => 3.8, 'rating' => 'AAA', 'ytm' => 5.50],
        ];
        foreach ($obligasis as $o) {
            AnalisaObligasi::create(array_merge($o, ['analisa_reksa_dana_id' => $draft->id]));
        }

        // Alokasi Aset
        $alokasis = [
            ['nama_aset' => 'Saham', 'persentase' => 75.00],
            ['nama_aset' => 'Obligasi', 'persentase' => 10.00],
            ['nama_aset' => 'Pasar Uang', 'persentase' => 8.00],
            ['nama_aset' => 'Kas', 'persentase' => 5.00],
            ['nama_aset' => 'Lainnya', 'persentase' => 2.00],
        ];
        foreach ($alokasis as $a) {
            AnalisaAlokasiAset::create(array_merge($a, ['analisa_reksa_dana_id' => $draft->id]));
        }

        $this->command->info('Created sample draft for RD001 with child records.');
        $this->command->info('Local test data seeding selesai!');
    }

    private function makePdf(string $textContent): string
    {
        $textContent = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $textContent);
        $textContent = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $textContent);

        $pages = [];
        $maxLen = 1500;
        $chunks = str_split($textContent, $maxLen);
        $objNum = 1;

        $catalog = "{$objNum} 0 obj\n<< /Type /Catalog /Pages " . ($objNum + 1) . " 0 R >>\nendobj\n";
        $objNum++;
        $pageCount = count($chunks);
        $kidIds = [];
        $contentObjs = [];

        foreach ($chunks as $i => $chunk) {
            $contentId = $objNum;
            $objNum++;
            $contentStream = "BT /F1 10 Tf 50 750 Td (" . $chunk . ") Tj ET";
            $contentObjs[] = "{$contentId} 0 obj\n<< /Length " . strlen($contentStream) . " >>\nstream\n{$contentStream}\nendstream\nendobj\n";

            $pageId = $objNum;
            $objNum++;
            $kidIds[] = $pageId;
            $pages[] = "{$pageId} 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents {$contentId} 0 R /Resources << /Font << /F1 3 0 R >> >> >>\nendobj\n";
        }

        $pagesObj = "2 0 obj\n<< /Type /Pages /Kids [" . implode(' ', $kidIds) . "] /Count {$pageCount} >>\nendobj\n";
        $fontObj = "3 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Courier >>\nendobj\n";

        $body = $catalog . $pagesObj . $fontObj;
        foreach ($contentObjs as $co) {
            $body .= $co;
        }
        foreach ($pages as $p) {
            $body .= $p;
        }

        $offsets = [];
        $raw = "%PDF-1.4\n";
        $offsets[] = strlen($raw);
        $raw .= $body;
        $finalObjNum = $objNum;
        $raw .= "xref\n0 {$finalObjNum}\n0000000000 65535 f \n";

        $pos = 0;
        $lines = explode("\n", $raw);
        $linePositions = [];
        $currentPos = 0;
        foreach ($lines as $line) {
            $linePositions[] = $currentPos;
            $currentPos += strlen($line) + 1;
        }

        $objPos = 0;
        $rawLines = explode("\n", $raw);
        $offsetIdx = 0;
        $startxref = 0;

        $final = "%PDF-1.4\n";
        $currentOffset = strlen($final);

        $final .= $body;
        $bodyEnd = strlen($final);

        $entries = [];
        $entries[] = sprintf("%010d 65535 f \n", 0);
        for ($i = 1; $i < $finalObjNum; $i++) {
            $entries[] = sprintf("%010d 00000 n \n", $currentOffset);
            $currentOffset = $bodyEnd;
        }

        $xrefOffset = strlen($final);
        $final .= "xref\n0 {$finalObjNum}\n";
        foreach ($entries as $entry) {
            $final .= $entry;
        }
        $final .= "trailer\n<< /Size {$finalObjNum} /Root 1 0 R >>\nstartxref\n{$xrefOffset}\n%%EOF\n";

        return $final;
    }
}
