<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Imports\AnalisaImportPreview;
use App\Imports\LegacyFormatReader;
use Maatwebsite\Excel\Facades\Excel;

class TestImportExcel extends Command
{
    protected $signature = 'example:test-import {file?}';
    protected $description = 'Test Excel import and report which fields are valid/missing';

    public function handle()
    {
        $path = $this->argument('file') ?: public_path('storage/example/example-full-test.xlsx');

        if (!file_exists($path)) {
            $this->error("File not found: {$path}");
            return 1;
        }

        $this->info("Testing: {$path}\n");

        // ─── Step 1: Portfolio sheets ───────────────────────────
        $this->line('─── Step 1: Portfolio Sheets (AnalisaImportPreview) ───');
        $portfolioData = [];
        try {
            $import = new AnalisaImportPreview;
            Excel::import($import, $path);
            $portfolioData = $import->getData();
            foreach ($portfolioData as $name => $rows) {
                $status = count($rows) > 0 ? '✅' : '⚠️';
                $this->line(" {$status} Sheet '{$name}': " . count($rows) . ' rows');
            }
        } catch (\Throwable $e) {
            $this->warn(" ⚠️  Portfolio sheets not found (expected for financial-only files)");
        }

        // ─── Step 2: Financial statements ───────────────────────────
        $this->line("\n─── Step 2: Financial Statements (LegacyFormatReader) ───");
        $legacyData = [];
        try {
            $reader = new LegacyFormatReader;
            $legacyData = $reader->read($path);
        } catch (\Throwable $e) {
            $this->error(" Error: " . $e->getMessage());
        }

        $merged = array_merge($legacyData, $portfolioData);

        // Report which financial sheets were found
        $expectedSheets = ['Posisi Keuangan', 'Laba Rugi', 'Arus Kas', 'Ringkasan', 'Perubahan Aset Bersih'];
        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
            foreach ($expectedSheets as $sn) {
                $found = in_array($sn, $spreadsheet->getSheetNames());
                $this->line($found ? " ✅ Sheet '{$sn}' found" : " ❌ Sheet '{$sn}' NOT found");
            }
        } catch (\Throwable $e) {}

        // ─── Step 3: Scalar fields validation ───────────────────────────
        $this->line("\n─── Step 3: Scalar Field Validation ───");

        $formFields = [
            'Informasi' => [
                'nama_reksa_dana'  => 'Nama Reksa Dana',
                'kode_reksa_dana'  => 'Kode RD',
                'jenis_reksa_dana' => 'Jenis RD',
                'manajer_investasi'=> 'Manajer Investasi',
                'bank_kustodian'   => 'Bank Kustodian',
            ],
            'Data' => [
                'tanggal_data'   => 'Tanggal Data',
                'total_aum'      => 'Total AUM',
                'nab_per_unit'   => 'NAB/UP',
                'unit_penyertaan'=> 'Unit Penyertaan',
                'return_ytd'     => 'Return YTD',
                'return_1y'      => 'Return 1Y',
            ],
            'Neraca' => [
                'total_aset'                          => 'Total Aset',
                'total_liabilitas'                    => 'Total Liabilitas',
                'nilai_aset_bersih'                   => 'Nilai Aset Bersih',
                'kas_dan_bank'                        => 'Kas dan Bank',
                'piutang_bunga'                       => 'Piutang Bunga',
                'piutang_dividen'                     => 'Piutang Dividen',
                'piutang_lain'                        => 'Piutang Lain',
                'utang_pajak'                         => 'Utang Pajak',
                'utang_lain'                          => 'Utang Lain',
                'portofolio_efek'                     => 'Portofolio Efek',
                'instrumen_pasar_uang'                => 'Instrumen Pasar Uang',
                'piutang_transaksi_efek'              => 'Piutang Transaksi Efek',
                'uang_muka_diterima'                  => 'Uang Muka Diterima',
                'liabilitas_pembelian_kembali'         => 'Liabilitas Pembelian Kembali',
                'beban_akrual'                        => 'Beban Akrual',
                'liabilitas_atas_biaya'               => 'Liabilitas Atas Biaya',
            ],
            'Laba Rugi' => [
                'pendapatan_bunga'      => 'Pendapatan Bunga',
                'pendapatan_dividen'    => 'Pendapatan Dividen',
                'gain_realized'         => 'Gain Realized',
                'gain_unrealized'       => 'Gain Unrealized',
                'beban_kustodian'       => 'Beban Kustodian',
                'beban_lain'            => 'Beban Lain',
                'laba_bersih'           => 'Laba Bersih',
                'laba_sebelum_pajak'    => 'Laba Sebelum Pajak',
                'beban_pajak_penghasilan'=> 'Beban Pajak',
                'laba_bersih_tahun_berjalan' => 'Laba Tahun Berjalan',
                'total_pendapatan'      => 'Total Pendapatan',
                'beban_pengelolaan_investasi' => 'Beban Pengelolaan Investasi',
            ],
            'Arus Kas' => [
                'arus_kas_operasi'   => 'Arus Kas Operasi',
                'arus_kas_pendanaan' => 'Arus Kas Pendanaan',
                'kas_awal_tahun'     => 'Kas Awal Tahun',
                'kas_akhir_tahun'    => 'Kas Akhir Tahun',
                'penerimaan_bunga_deposito' => 'Penerimaan Bunga Deposito',
                'penerimaan_dividen_kas'   => 'Penerimaan Dividen',
                'penjualan_efek_ekuitas'   => 'Penjualan Efek',
                'pembelian_efek_ekuitas'   => 'Pembelian Efek',
                'beban_investasi'          => 'Beban Investasi',
                'penerimaan_penjualan_unit' => 'Penjualan Unit',
                'pembayaran_pembelian_kembali_unit' => 'Pembelian Kembali Unit',
            ],
            'Rasio' => [
                'total_hasil_investasi' => 'Total Hasil Investasi',
                'total_unit_beredar'    => 'Total Unit Beredar',
            ],
        ];

        $totalOk = 0;
        $totalMissing = 0;
        $totalFields = 0;
        foreach ($formFields as $group => $fields) {
            $groupOk = 0;
            $this->line("\n {$group}:");
            foreach ($fields as $key => $label) {
                $totalFields++;
                $value = $merged[$key] ?? null;
                if ($value !== null && $value !== '' && $value !== '-') {
                    $v = is_numeric($value) ? number_format((float) $value, 2) : $value;
                    $this->line("   ✅ {$label} ({$key}) = {$v}");
                    $groupOk++;
                } else {
                    $this->line("   ❌ {$label} ({$key}) = MISSING or empty");
                }
            }
            $totalOk += $groupOk;
            $totalMissing += count($fields) - $groupOk;
        }

        // ─── Summary ───────────────────────────
        $pct = $totalFields > 0 ? round($totalOk / $totalFields * 100) : 0;
        $this->newLine();
        $this->line(str_repeat('─', 50));
        $this->info(" RESULT: {$totalOk}/{$totalFields} fields filled ({$pct}%)");
        $this->line(str_repeat('─', 50));

        if ($totalMissing > 0) {
            $this->warn(" {$totalMissing} fields still empty — data not present in Excel or label mismatch.");
            $this->line(" Tips:");
            $this->line(" • Financial data comes from sheets: Posisi Keuangan, Laba Rugi, Arus Kas");
            $this->line(" • Portfolio data comes from sheets: Sektor, Efek, Kinerja, Obligasi, Sukuk, Bank");
            $this->line(" • Metadata (nama RD, jenis, MI) are NOT extracted from Excel; fill manually.");
        } else {
            $this->info(" All fields match! File is fully valid.");
        }

        return 0;
    }
}
