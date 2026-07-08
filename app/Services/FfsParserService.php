<?php

namespace App\Services;

use Smalot\PdfParser\Parser;

class FfsParserService
{
    public function parse(string $pdfPath): array
    {
        $parser = new Parser;
        $pdf = $parser->parseFile($pdfPath);
        $text = $pdf->getText();

        $lines = array_filter(array_map('trim', explode("\n", $text)));
        $fullText = implode("\n", $lines);

        $lk = $this->safeExtract('extractLaporanKeuangan', [$lines, $fullText]) ?? [];

        return [
            'nama_reksa_dana' => $this->safeExtract('extractNamaReksaDana', [$lines, $fullText]),
            'jenis_reksa_dana' => $this->safeExtract('extractJenisReksaDana', [$lines, $fullText]),
            'kategori' => [],
            'manajer_investasi' => $this->safeExtract('extractManajerInvestasi', [$lines, $fullText]),
            'bank_kustodian' => $this->safeExtract('extractBankKustodian', [$lines, $fullText]),
            'tanggal_peluncuran' => $this->safeExtract('extractTanggalPeluncuran', [$lines, $fullText]),
            'mata_uang' => $this->safeExtract('extractMataUang', [$lines, $fullText]),
            'benchmark' => $this->safeExtract('extractBenchmark', [$lines, $fullText]),
            'tujuan_investasi' => $this->safeExtract('extractTujuanInvestasi', [$lines, $fullText]),
            'kebijakan_investasi' => $this->safeExtract('extractKebijakanInvestasi', [$lines, $fullText]),
            'total_aum' => $this->safeExtract('extractAum', [$lines, $fullText]),
            'unit_penyertaan' => $this->safeExtract('extractUnitPenyertaan', [$lines, $fullText]),
            'nab_per_unit' => $this->safeExtract('extractNabPerUnit', [$lines, $fullText]),
            'total_marcap_10_efek' => $this->safeExtract('extractTotalMarcap', [$lines, $fullText]),
            'tanggal_data' => $this->safeExtract('extractTanggalData', [$lines, $fullText]),
            'ffs_bulan' => null,
            'ffs_tahun' => null,
            'return_ytd' => $this->safeExtract('extractReturnYtd', [$lines, $fullText]),
            'return_1y' => $this->safeExtract('extractReturn1y', [$lines, $fullText]),
            'total_return' => $lk['total_return'] ?? null,
            'biaya_operasi' => $lk['biaya_operasi'] ?? null,
            'portfolio_turnover_ratio' => $lk['portfolio_turnover_ratio'] ?? null,
            'management_fee' => $this->safeExtract('extractManagementFee', [$lines, $fullText]),
            'custodian_fee' => $this->safeExtract('extractCustodianFee', [$lines, $fullText]),
            'total_aset' => $lk['total_aset'] ?? null,
            'total_liabilitas' => $lk['total_liabilitas'] ?? null,
            'kas_dan_bank' => $lk['kas_dan_bank'] ?? null,
            'piutang_bunga' => $lk['piutang_bunga'] ?? null,
            'piutang_dividen' => $lk['piutang_dividen'] ?? null,
            'piutang_lain' => $lk['piutang_lain'] ?? null,
            'utang_pajak' => $lk['utang_pajak'] ?? null,
            'utang_lain' => $lk['utang_lain'] ?? null,
            'pendapatan_bunga' => $lk['pendapatan_bunga'] ?? null,
            'pendapatan_dividen' => $lk['pendapatan_dividen'] ?? null,
            'gain_realized' => $lk['gain_realized'] ?? null,
            'gain_unrealized' => $lk['gain_unrealized'] ?? null,
            'beban_mi' => $lk['beban_mi'] ?? null,
            'beban_kustodian' => $lk['beban_kustodian'] ?? null,
            'beban_lain' => $lk['beban_lain'] ?? null,
            'laba_bersih' => $lk['laba_bersih'] ?? null,
            'arus_kas_operasi' => $lk['arus_kas_operasi'] ?? null,
            'arus_kas_pendanaan' => $lk['arus_kas_pendanaan'] ?? null,
            'kas_awal_tahun' => $lk['kas_awal_tahun'] ?? null,
            'kas_akhir_tahun' => $lk['kas_akhir_tahun'] ?? null,
            'portofolio_efek' => $lk['portofolio_efek'] ?? null,
            'instrumen_pasar_uang' => $lk['instrumen_pasar_uang'] ?? null,
            'piutang_transaksi_efek' => $lk['piutang_transaksi_efek'] ?? null,
            'piutang_bunga_dan_dividen' => $lk['piutang_bunga_dan_dividen'] ?? null,
            'uang_muka_diterima' => $lk['uang_muka_diterima'] ?? null,
            'liabilitas_pembelian_kembali' => $lk['liabilitas_pembelian_kembali'] ?? null,
            'beban_akrual' => $lk['beban_akrual'] ?? null,
            'liabilitas_atas_biaya' => $lk['liabilitas_atas_biaya'] ?? null,
            'pembelian_kembali_unit_penyertaan' => $lk['pembelian_kembali_unit_penyertaan'] ?? null,
            'utang_pajak_lainnya' => $lk['utang_pajak_lainnya'] ?? null,
            'pendapatan_investasi' => $lk['pendapatan_investasi'] ?? null,
            'pendapatan_lainnya' => $lk['pendapatan_lainnya'] ?? null,
            'beban_investasi' => $lk['beban_investasi'] ?? null,
            'beban_pengelolaan_investasi' => $lk['beban_pengelolaan_investasi'] ?? null,
            'pembelian_efek_ekuitas' => $lk['pembelian_efek_ekuitas'] ?? null,
            'penjualan_efek_ekuitas' => $lk['penjualan_efek_ekuitas'] ?? null,
            'penerimaan_bunga_deposito' => $lk['penerimaan_bunga_deposito'] ?? null,
            'penerimaan_bunga_jasa_giro' => $lk['penerimaan_bunga_jasa_giro'] ?? null,
            'penerimaan_dividen_kas' => $lk['penerimaan_dividen_kas'] ?? null,
            'pembayaran_jasa_pengelolaan' => $lk['pembayaran_jasa_pengelolaan'] ?? null,
            'pembayaran_jasa_kustodian' => $lk['pembayaran_jasa_kustodian'] ?? null,
            'pembayaran_beban_lain_arus' => $lk['pembayaran_beban_lain_arus'] ?? null,
            'kas_bersih_aktivitas_operasi' => $lk['kas_bersih_aktivitas_operasi'] ?? null,
            'penerimaan_penjualan_unit' => $lk['penerimaan_penjualan_unit'] ?? null,
            'pembayaran_pembelian_kembali_unit' => $lk['pembayaran_pembelian_kembali_unit'] ?? null,
            'kas_bersih_aktivitas_pendanaan' => $lk['kas_bersih_aktivitas_pendanaan'] ?? null,
            'kenaikan_kas_setara_kas' => $lk['kenaikan_kas_setara_kas'] ?? null,
            'total_hasil_investasi' => $lk['total_hasil_investasi'] ?? null,
            'hasil_investasi_setelah_biaya' => $lk['hasil_investasi_setelah_biaya'] ?? null,
            'persentase_pph' => $lk['persentase_pph'] ?? null,
            'fair_value_level_1' => $lk['fair_value_level_1'] ?? null,
            'fair_value_level_2' => $lk['fair_value_level_2'] ?? null,
            'fair_value_level_3' => $lk['fair_value_level_3'] ?? null,
            'unit_milik_investor' => $lk['unit_milik_investor'] ?? null,
            'unit_milik_mi' => $lk['unit_milik_mi'] ?? null,
            'total_unit_beredar' => $lk['total_unit_beredar'] ?? null,
            'alokasi_aset' => $this->safeExtract('extractAlokasiAset', [$lines, $fullText]),
            'sektor' => $this->safeExtract('extractSektor', [$lines, $fullText]),
            'efek' => $this->safeExtract('extractEfek', [$lines, $fullText]),
            'kinerja' => $this->safeExtract('extractKinerja', [$lines, $fullText]),
            'obligasi' => $this->safeExtract('extractObligasi', [$lines, $fullText]),
            'sukuk' => $this->safeExtract('extractSukuk', [$lines, $fullText]),
            'bank' => $this->safeExtract('extractBank', [$lines, $fullText]),
            'pasar_uang' => [],
            'piutang_bunga_detail' => [],
        ];
    }

    public function parseWithAi(string $pdfPath, GroqService $groq, ?string $documentType = null): array
    {
        set_time_limit(300);
        $parser = new Parser;
        $pdf = $parser->parseFile($pdfPath);
        $text = $pdf->getText();

        $lines = array_filter(array_map('trim', explode("\n", $text)));
        $fullText = implode("\n", $lines);

        $lk = $this->safeExtract('extractLaporanKeuangan', [$lines, $fullText]) ?? [];

        // Log keyword LK yang ditemukan dalam teks PDF
        $lkKeywords = ['total aset','total liabilitas','kas dan bank','piutang bunga','piutang dividen',
            'piutang lain','utang pajak','utang lain','pendapatan bunga','pendapatan dividen',
            'gain realized','gain unrealized','beban mi','beban kustodian','beban lain',
            'laba bersih','arus kas operasi','arus kas pendanaan','kas awal','kas akhir',
            'total hasil investasi','hasil investasi setelah','portfolio turnover','penghasilan kena pajak',
            'fair value','nilai wajar','unit milik investor','unit milik mi','total unit beredar'];
        $foundKeywords = [];
        $lowerText = strtolower($fullText);
        foreach ($lkKeywords as $kw) {
            if (str_contains($lowerText, $kw)) $foundKeywords[] = $kw;
        }
        \Log::info('[PARSE] LK keywords found in text: ' . json_encode($foundKeywords));
        \Log::info('[PARSE] Text length: ' . strlen($fullText) . ' chars, lines: ' . count($lines));
        // Log 10 baris terakhir dari teks (biasanya LK ada di akhir)
        $lastLines = array_slice($lines, -15);
        \Log::info('[PARSE] Last 15 lines of text: ' . json_encode($lastLines));

        $regex = [
            'nama_reksa_dana' => $this->safeExtract('extractNamaReksaDana', [$lines, $fullText]),
            'jenis_reksa_dana' => $this->safeExtract('extractJenisReksaDana', [$lines, $fullText]),
            'kategori' => [],
            'manajer_investasi' => $this->safeExtract('extractManajerInvestasi', [$lines, $fullText]),
            'bank_kustodian' => $this->safeExtract('extractBankKustodian', [$lines, $fullText]),
            'tanggal_peluncuran' => $this->safeExtract('extractTanggalPeluncuran', [$lines, $fullText]),
            'mata_uang' => $this->safeExtract('extractMataUang', [$lines, $fullText]),
            'benchmark' => $this->safeExtract('extractBenchmark', [$lines, $fullText]),
            'tujuan_investasi' => $this->safeExtract('extractTujuanInvestasi', [$lines, $fullText]),
            'kebijakan_investasi' => $this->safeExtract('extractKebijakanInvestasi', [$lines, $fullText]),
            'total_aum' => $this->safeExtract('extractAum', [$lines, $fullText]),
            'unit_penyertaan' => $this->safeExtract('extractUnitPenyertaan', [$lines, $fullText]),
            'nab_per_unit' => $this->safeExtract('extractNabPerUnit', [$lines, $fullText]),
            'total_marcap_10_efek' => $this->safeExtract('extractTotalMarcap', [$lines, $fullText]),
            'tanggal_data' => $this->safeExtract('extractTanggalData', [$lines, $fullText]),
            'ffs_bulan' => null,
            'ffs_tahun' => null,
            'return_ytd' => $this->safeExtract('extractReturnYtd', [$lines, $fullText]),
            'return_1y' => $this->safeExtract('extractReturn1y', [$lines, $fullText]),
            'total_return' => $lk['total_return'] ?? null,
            'biaya_operasi' => $lk['biaya_operasi'] ?? null,
            'portfolio_turnover_ratio' => $lk['portfolio_turnover_ratio'] ?? null,
            'management_fee' => $this->safeExtract('extractManagementFee', [$lines, $fullText]),
            'custodian_fee' => $this->safeExtract('extractCustodianFee', [$lines, $fullText]),
            'total_aset' => $lk['total_aset'] ?? null,
            'total_liabilitas' => $lk['total_liabilitas'] ?? null,
            'kas_dan_bank' => $lk['kas_dan_bank'] ?? null,
            'piutang_bunga' => $lk['piutang_bunga'] ?? null,
            'piutang_dividen' => $lk['piutang_dividen'] ?? null,
            'piutang_lain' => $lk['piutang_lain'] ?? null,
            'utang_pajak' => $lk['utang_pajak'] ?? null,
            'utang_lain' => $lk['utang_lain'] ?? null,
            'pendapatan_bunga' => $lk['pendapatan_bunga'] ?? null,
            'pendapatan_dividen' => $lk['pendapatan_dividen'] ?? null,
            'gain_realized' => $lk['gain_realized'] ?? null,
            'gain_unrealized' => $lk['gain_unrealized'] ?? null,
            'beban_mi' => $lk['beban_mi'] ?? null,
            'beban_kustodian' => $lk['beban_kustodian'] ?? null,
            'beban_lain' => $lk['beban_lain'] ?? null,
            'laba_bersih' => $lk['laba_bersih'] ?? null,
            'arus_kas_operasi' => $lk['arus_kas_operasi'] ?? null,
            'arus_kas_pendanaan' => $lk['arus_kas_pendanaan'] ?? null,
            'kas_awal_tahun' => $lk['kas_awal_tahun'] ?? null,
            'kas_akhir_tahun' => $lk['kas_akhir_tahun'] ?? null,
            'portofolio_efek' => $lk['portofolio_efek'] ?? null,
            'instrumen_pasar_uang' => $lk['instrumen_pasar_uang'] ?? null,
            'piutang_transaksi_efek' => $lk['piutang_transaksi_efek'] ?? null,
            'piutang_bunga_dan_dividen' => $lk['piutang_bunga_dan_dividen'] ?? null,
            'uang_muka_diterima' => $lk['uang_muka_diterima'] ?? null,
            'liabilitas_pembelian_kembali' => $lk['liabilitas_pembelian_kembali'] ?? null,
            'beban_akrual' => $lk['beban_akrual'] ?? null,
            'liabilitas_atas_biaya' => $lk['liabilitas_atas_biaya'] ?? null,
            'pembelian_kembali_unit_penyertaan' => $lk['pembelian_kembali_unit_penyertaan'] ?? null,
            'utang_pajak_lainnya' => $lk['utang_pajak_lainnya'] ?? null,
            'pendapatan_investasi' => $lk['pendapatan_investasi'] ?? null,
            'pendapatan_lainnya' => $lk['pendapatan_lainnya'] ?? null,
            'beban_investasi' => $lk['beban_investasi'] ?? null,
            'beban_pengelolaan_investasi' => $lk['beban_pengelolaan_investasi'] ?? null,
            'pembelian_efek_ekuitas' => $lk['pembelian_efek_ekuitas'] ?? null,
            'penjualan_efek_ekuitas' => $lk['penjualan_efek_ekuitas'] ?? null,
            'penerimaan_bunga_deposito' => $lk['penerimaan_bunga_deposito'] ?? null,
            'penerimaan_bunga_jasa_giro' => $lk['penerimaan_bunga_jasa_giro'] ?? null,
            'penerimaan_dividen_kas' => $lk['penerimaan_dividen_kas'] ?? null,
            'pembayaran_jasa_pengelolaan' => $lk['pembayaran_jasa_pengelolaan'] ?? null,
            'pembayaran_jasa_kustodian' => $lk['pembayaran_jasa_kustodian'] ?? null,
            'pembayaran_beban_lain_arus' => $lk['pembayaran_beban_lain_arus'] ?? null,
            'kas_bersih_aktivitas_operasi' => $lk['kas_bersih_aktivitas_operasi'] ?? null,
            'penerimaan_penjualan_unit' => $lk['penerimaan_penjualan_unit'] ?? null,
            'pembayaran_pembelian_kembali_unit' => $lk['pembayaran_pembelian_kembali_unit'] ?? null,
            'kas_bersih_aktivitas_pendanaan' => $lk['kas_bersih_aktivitas_pendanaan'] ?? null,
            'kenaikan_kas_setara_kas' => $lk['kenaikan_kas_setara_kas'] ?? null,
            'total_hasil_investasi' => $lk['total_hasil_investasi'] ?? null,
            'hasil_investasi_setelah_biaya' => $lk['hasil_investasi_setelah_biaya'] ?? null,
            'persentase_pph' => $lk['persentase_pph'] ?? null,
            'fair_value_level_1' => $lk['fair_value_level_1'] ?? null,
            'fair_value_level_2' => $lk['fair_value_level_2'] ?? null,
            'fair_value_level_3' => $lk['fair_value_level_3'] ?? null,
            'unit_milik_investor' => $lk['unit_milik_investor'] ?? null,
            'unit_milik_mi' => $lk['unit_milik_mi'] ?? null,
            'total_unit_beredar' => $lk['total_unit_beredar'] ?? null,
            'alokasi_aset' => $this->safeExtract('extractAlokasiAset', [$lines, $fullText]),
            'sektor' => $this->safeExtract('extractSektor', [$lines, $fullText]),
            'efek' => $this->safeExtract('extractEfek', [$lines, $fullText]),
            'kinerja' => $this->safeExtract('extractKinerja', [$lines, $fullText]),
            'obligasi' => $this->safeExtract('extractObligasi', [$lines, $fullText]),
            'sukuk' => $this->safeExtract('extractSukuk', [$lines, $fullText]),
            'bank' => $this->safeExtract('extractBank', [$lines, $fullText]),
            'pasar_uang' => [],
            'piutang_bunga_detail' => [],
        ];

        // Vision fallback jika text terlalu pendek (scanned PDF)
        if (mb_strlen($fullText) < 500) {
            $ai = $groq->parseFfsPdfVision($pdfPath, basename($pdfPath));
            \Log::info('[PARSE-VISION] AI LK fields: ' . json_encode([
                'total_aset' => $ai['total_aset'] ?? null,
                'laba_bersih' => $ai['laba_bersih'] ?? null,
                'arus_kas_operasi' => $ai['arus_kas_operasi'] ?? null,
                'total_hasil_investasi' => $ai['total_hasil_investasi'] ?? null,
                'unit_milik_investor' => $ai['unit_milik_investor'] ?? null,
            ]));
            $merged = $this->merge($regex, $this->normalizeAiData($ai));
            \Log::info('[PARSE-VISION] Merged LK fields: ' . json_encode([
                'total_aset' => $merged['total_aset'] ?? null,
                'laba_bersih' => $merged['laba_bersih'] ?? null,
            ]));
            return $merged;
        }

        // Sampling teks dari seluruh halaman agar AI lihat semua bagian dokumen
        $maxChars = 60000;
        $sampled = '';

        $pageTexts = [];
        try {
            foreach ($pdf->getPages() as $page) {
                $pageTexts[] = $page->getText();
            }
        } catch (\Throwable) {
            $pageTexts = [];
        }

        // Smart text trimming per document_type: send only relevant pages
        if ($documentType && !empty($pageTexts)) {
            $totalPages = count($pageTexts);
            $sampled = match ($documentType) {
                // Informasi lainnya: halaman awal (identitas RD, info umum)
                'informasi_lainnya' => implode("\n\n", array_slice($pageTexts, 0, min(5, $totalPages))),
                // Portofolio efek: halaman 2 sampai akhir (holdings ada di mana saja)
                'portofolio_efek' => implode("\n\n", array_slice($pageTexts, min(1, $totalPages - 1))),
                // Pengukuran nilai wajar: halaman 40% ke belakang (catatan LK)
                'pengukuran_nilai_wajar' => implode("\n\n", array_slice($pageTexts, max(0, (int)($totalPages * 0.4)))),
                // BS/IS/CF/PUP: halaman 30% ke belakang (LK biasanya di paruh kedua)
                'bs_is_cf_pup' => implode("\n\n", array_slice($pageTexts, max(0, (int)($totalPages * 0.3)))),
                default => '',
            };
            if ($sampled) {
                $ai = match ($documentType) {
                    'informasi_lainnya' => $groq->parseInformasiLainnya($sampled),
                    'portofolio_efek' => $groq->parsePortofolioEfek($sampled),
                    'pengukuran_nilai_wajar' => $groq->parsePengukuranNilaiWajar($sampled),
                    'bs_is_cf_pup' => $groq->parseBsIsCfPup($sampled),
                    default => $groq->parseFfsPdf($sampled, $documentType),
                };
                return $this->merge($regex, $this->normalizeAiData($ai));
            }
        }

        // Jika page-level gagal, fallback ke fullText polos
        if (empty($pageTexts)) {
            $sampled = mb_substr($fullText, 0, $maxChars);
        } else {
            $totalPages = count($pageTexts);

            // Bagian 1: halaman 1-5 (identitas reksa dana) — ambil full
            $end = min(5, $totalPages);
            for ($i = 0; $i < $end; $i++) {
                $sampled .= $pageTexts[$i] . "\n\n";
            }

            // Bagian 2: halaman 6-40 (portofolio, holdings)
            if ($totalPages > 5) {
                $budget = (int)(($maxChars - strlen($sampled)) * 0.6);
                $text = '';
                for ($i = 5; $i < min(40, $totalPages); $i++) {
                    $text .= $pageTexts[$i] . "\n\n";
                }
                if (strlen($text) > $budget) {
                    $text = mb_substr($text, 0, max(0, $budget));
                }
                $sampled .= $text;
            }

            // Bagian 3: halaman 40+ (laporan keuangan)
            if ($totalPages > 40) {
                $remaining = $maxChars - strlen($sampled) - 1000;
                if ($remaining > 0) {
                    $text = '';
                    for ($i = 40; $i < $totalPages; $i++) {
                        $text .= $pageTexts[$i] . "\n\n";
                    }
                    $sampled .= mb_substr($text, 0, $remaining);
                }
            }

            $sampled = mb_substr($sampled, 0, $maxChars);
        }

        $ai = $groq->parseFfsPdf($sampled, $documentType);

        \Log::info('[PARSE] AI response keys: ' . json_encode(array_keys($ai)));
        \Log::info('[PARSE] AI LK fields: ' . json_encode([
            'total_aset' => $ai['total_aset'] ?? null,
            'total_liabilitas' => $ai['total_liabilitas'] ?? null,
            'kas_dan_bank' => $ai['kas_dan_bank'] ?? null,
            'piutang_bunga' => $ai['piutang_bunga'] ?? null,
            'piutang_dividen' => $ai['piutang_dividen'] ?? null,
            'piutang_lain' => $ai['piutang_lain'] ?? null,
            'utang_pajak' => $ai['utang_pajak'] ?? null,
            'utang_lain' => $ai['utang_lain'] ?? null,
            'pendapatan_bunga' => $ai['pendapatan_bunga'] ?? null,
            'pendapatan_dividen' => $ai['pendapatan_dividen'] ?? null,
            'gain_realized' => $ai['gain_realized'] ?? null,
            'gain_unrealized' => $ai['gain_unrealized'] ?? null,
            'beban_mi' => $ai['beban_mi'] ?? null,
            'beban_kustodian' => $ai['beban_kustodian'] ?? null,
            'beban_lain' => $ai['beban_lain'] ?? null,
            'laba_bersih' => $ai['laba_bersih'] ?? null,
            'arus_kas_operasi' => $ai['arus_kas_operasi'] ?? null,
            'arus_kas_pendanaan' => $ai['arus_kas_pendanaan'] ?? null,
            'kas_awal_tahun' => $ai['kas_awal_tahun'] ?? null,
            'kas_akhir_tahun' => $ai['kas_akhir_tahun'] ?? null,
            'total_hasil_investasi' => $ai['total_hasil_investasi'] ?? null,
            'hasil_investasi_setelah_biaya' => $ai['hasil_investasi_setelah_biaya'] ?? null,
            'persentase_pph' => $ai['persentase_pph'] ?? null,
            'fair_value_level_1' => $ai['fair_value_level_1'] ?? null,
            'fair_value_level_2' => $ai['fair_value_level_2'] ?? null,
            'fair_value_level_3' => $ai['fair_value_level_3'] ?? null,
            'unit_milik_investor' => $ai['unit_milik_investor'] ?? null,
            'unit_milik_mi' => $ai['unit_milik_mi'] ?? null,
            'total_unit_beredar' => $ai['total_unit_beredar'] ?? null,
        ]));

        $merged = $this->merge($regex, $this->normalizeAiData($ai));

        \Log::info('[PARSE] Merged LK fields: ' . json_encode([
            'total_aset' => $merged['total_aset'] ?? null,
            'total_liabilitas' => $merged['total_liabilitas'] ?? null,
            'laba_bersih' => $merged['laba_bersih'] ?? null,
            'arus_kas_operasi' => $merged['arus_kas_operasi'] ?? null,
        ]));

        // Vision fallback jika field financial masih kosong (tabel rusak di text extraction)
        $essentialLkFields = ['total_aset', 'total_liabilitas', 'laba_bersih', 'arus_kas_operasi', 'kas_dan_bank'];
        $filled = array_filter($essentialLkFields, fn($f) => !empty($merged[$f]));
        $ratioUnitFields = ['total_hasil_investasi', 'hasil_investasi_setelah_biaya', 'persentase_pph',
                            'biaya_operasi', 'portfolio_turnover_ratio', 'unit_milik_investor',
                            'unit_milik_mi', 'total_unit_beredar'];
        $filledRatio = array_filter($ratioUnitFields, fn($f) => !empty($merged[$f]));
        if (count($filled) < 2 || count($filledRatio) < 3) {
            \Log::info('[PARSE] Financial fields minimal (' . count($filled) . '/5 essential, '
                . count($filledRatio) . '/8 ratio+unit), jalankan vision fallback');
            try {
                $vision = $groq->parseProspektusFinancialVision($pdfPath);
                $visionFields = array_keys(array_filter($vision, fn($v) => $v !== null && $v !== '' && $v !== []));
                \Log::info('[PARSE-VISION] Vision hasil: ' . count($visionFields) . ' fields: ' . json_encode($visionFields));
                $merged = $this->merge($merged, $vision);
            } catch (\Throwable $e) {
                \Log::warning('[PARSE-VISION] Vision fallback gagal: ' . $e->getMessage());
            }
        }

        return $merged;
    }

    public function normalizeAiParseResult(array $ai): array
    {
        return $this->normalizeAiData($ai);
    }

    private function merge(array $regex, array $ai): array
    {
        $aiPreferredArrayFields = ['sektor', 'efek', 'kinerja', 'obligasi', 'sukuk', 'bank', 'alokasi_aset', 'pasar_uang', 'piutang_bunga_detail'];

        $aiPreferredScalarFields = [
            'total_aset', 'total_liabilitas', 'kas_dan_bank',
            'piutang_bunga', 'piutang_dividen', 'piutang_lain',
            'utang_pajak', 'utang_lain',
            'pendapatan_bunga', 'pendapatan_dividen',
            'gain_realized', 'gain_unrealized',
            'beban_mi', 'beban_kustodian', 'beban_lain',
            'laba_bersih',
            'arus_kas_operasi', 'arus_kas_pendanaan',
            'kas_awal_tahun', 'kas_akhir_tahun',
            'portofolio_efek', 'instrumen_pasar_uang',
            'piutang_transaksi_efek', 'piutang_bunga_dan_dividen',
            'uang_muka_diterima', 'liabilitas_pembelian_kembali',
            'beban_akrual', 'liabilitas_atas_biaya',
            'pembelian_kembali_unit_penyertaan', 'utang_pajak_lainnya',
            'pendapatan_investasi', 'pendapatan_lainnya',
            'beban_investasi', 'beban_pengelolaan_investasi',
            'pembelian_efek_ekuitas', 'penjualan_efek_ekuitas',
            'penerimaan_bunga_deposito', 'penerimaan_bunga_jasa_giro',
            'penerimaan_dividen_kas', 'pembayaran_jasa_pengelolaan',
            'pembayaran_jasa_kustodian', 'pembayaran_beban_lain_arus',
            'kas_bersih_aktivitas_operasi', 'penerimaan_penjualan_unit',
            'pembayaran_pembelian_kembali_unit', 'kas_bersih_aktivitas_pendanaan',
            'kenaikan_kas_setara_kas',
            'total_hasil_investasi', 'hasil_investasi_setelah_biaya', 'persentase_pph',
            'fair_value_level_1', 'fair_value_level_2', 'fair_value_level_3',
            'unit_milik_investor', 'unit_milik_mi', 'total_unit_beredar',
            'biaya_operasi', 'portfolio_turnover_ratio', 'total_return',
        ];

        foreach ($regex as $key => $value) {
            $aiValue = $ai[$key] ?? null;

            if (in_array($key, $aiPreferredArrayFields)) {
                if (!empty($aiValue)) {
                    $regex[$key] = $aiValue;
                }
            } elseif (in_array($key, $aiPreferredScalarFields)) {
                if ($aiValue !== null && $aiValue !== '') {
                    // Terima nilai AI 0 secara eksplisit (mis. persentase_pph = 0 sah) hanya
                    // ketika regex tidak punya nilai yang lebih kuat. Normalkan format.
                    $isZeroAi = $aiValue === 0 || $aiValue === 0.0
                        || (is_string($aiValue) && preg_match('/^[.,]+$/', $aiValue) === 0 && preg_match('/^0*[.,]?0+$/', $aiValue));
                    if ($isZeroAi && !empty($value) && $value !== 0 && $value !== 0.0 && $value !== '0') {
                        // Jangan overwrite nilai regex non-zero dengan AI 0 (kemungkinan default AI)
                    } else {
                        $regex[$key] = $aiValue;
                    }
                }
            } else {
                if ($key === 'nama_reksa_dana' && $this->looksLikeFundName($aiValue)) {
                    $regex[$key] = $aiValue;
                    continue;
                }

                if (empty($value) && !empty($aiValue)) {
                    $regex[$key] = $aiValue;
                }
            }
        }

        return $regex;
    }

    private function enrichRows(string $type, array $regexRows, array $aiRows): array
    {
        if ($type === 'efek') {
            // Index AI rows by kode_efek for fast lookup
            $aiIndex = [];
            foreach ($aiRows as $row) {
                $kode = strtoupper($row['kode_efek'] ?? '');
                if ($kode) $aiIndex[$kode] = $row;
            }

            return array_map(function ($row) use ($aiIndex) {
                $kode = strtoupper($row['kode_efek'] ?? '');
                $ai = $aiIndex[$kode] ?? null;
                if ($ai) {
                    $row['sektor']             = $row['sektor']             ?? ($ai['sektor'] ?? '');
                    $row['kontribusi_kinerja'] = $row['kontribusi_kinerja'] ?? ($ai['kontribusi_kinerja'] ?? null);
                    $row['ihsg_contribution']  = $row['ihsg_contribution']  ?? ($ai['ihsg_contribution'] ?? null);
                    $row['market_cap']         = $row['market_cap']         ?? ($ai['market_cap'] ?? null);
                    $row['nilai_pasar']        = $row['nilai_pasar']        ?? ($ai['nilai_pasar'] ?? null);
                    $row['harga_perolehan']    = $row['harga_perolehan']    ?? ($ai['harga_perolehan'] ?? null);
                    $row['persen_nab']         = $row['persen_nab']         ?? ($ai['persen_nab'] ?? null);
                    $row['return_1m']          = $row['return_1m']          ?? ($ai['return_1m'] ?? null);
                    $row['return_3m']          = $row['return_3m']          ?? ($ai['return_3m'] ?? null);
                    $row['return_6m']          = $row['return_6m']          ?? ($ai['return_6m'] ?? null);
                    $row['return_1y']          = $row['return_1y']          ?? ($ai['return_1y'] ?? null);
                    $row['top_10']             = $row['top_10']             ?? ($ai['top_10'] ?? false);
                }
                return $row;
            }, $regexRows);
        }

        $identityKeys = [
            'obligasi' => ['kode_obligasi', 'nama_obligasi'],
            'sukuk' => ['kode_sukuk', 'nama_sukuk'],
            'bank' => ['nama_bank'],
            'sektor' => ['nama_sektor'],
        ];

        if (!isset($identityKeys[$type])) {
            return $regexRows;
        }

        $aiIndex = [];
        foreach ($aiRows as $row) {
            foreach ($identityKeys[$type] as $key) {
                $identity = $this->normalizeIdentity($row[$key] ?? '');
                if ($identity !== '') {
                    $aiIndex[$identity] = $row;
                }
            }
        }

        return array_map(function ($row) use ($identityKeys, $type, $aiIndex) {
            $ai = null;
            foreach ($identityKeys[$type] as $key) {
                $identity = $this->normalizeIdentity($row[$key] ?? '');
                if ($identity !== '' && isset($aiIndex[$identity])) {
                    $ai = $aiIndex[$identity];
                    break;
                }
            }

            if ($ai) {
                foreach ($ai as $key => $value) {
                    if (($row[$key] ?? null) === null || ($row[$key] ?? '') === '') {
                        $row[$key] = $value;
                    }
                }
            }

            return $row;
        }, $regexRows);
    }

    private function normalizeIdentity(mixed $value): string
    {
        return strtoupper(preg_replace('/[^A-Z0-9]+/i', '', (string) $value));
    }

    private function looksLikeFundName(mixed $value): bool
    {
        $name = trim((string) $value);
        if (strlen($name) < 5) {
            return false;
        }

        $lower = strtolower($name);
        $generic = ['fund fact sheet', 'factsheet', 'fact sheet', 'laporan bulanan', 'monthly report'];

        return !in_array($lower, $generic, true);
    }

    private function normalizeAiData(array $data): array
    {
        $defaults = [
            'nama_reksa_dana' => null,
            'jenis_reksa_dana' => null,
            'kategori' => [],
            'manajer_investasi' => null,
            'bank_kustodian' => null,
            'tanggal_peluncuran' => null,
            'mata_uang' => null,
            'benchmark' => null,
            'tujuan_investasi' => null,
            'kebijakan_investasi' => null,
            'total_aum' => null,
            'unit_penyertaan' => null,
            'nab_per_unit' => null,
            'total_marcap_10_efek' => null,
            'tanggal_data' => null,
            'ffs_bulan' => null,
            'ffs_tahun' => null,
            'return_ytd' => null,
            'return_1y' => null,
            'total_return' => null,
            'biaya_operasi' => null,
            'portfolio_turnover_ratio' => null,
            'management_fee' => null,
            'custodian_fee' => null,
            'total_aset' => null,
            'total_liabilitas' => null,
            'kas_dan_bank' => null,
            'piutang_bunga' => null,
            'piutang_dividen' => null,
            'piutang_lain' => null,
            'utang_pajak' => null,
            'utang_lain' => null,
            'pendapatan_bunga' => null,
            'pendapatan_dividen' => null,
            'gain_realized' => null,
            'gain_unrealized' => null,
            'beban_mi' => null,
            'beban_kustodian' => null,
            'beban_lain' => null,
            'laba_bersih' => null,
            'total_beban' => null,
            'laba_sebelum_pajak' => null,
            'beban_pajak_penghasilan' => null,
            'laba_bersih_tahun_berjalan' => null,
            'penghasilan_komprehensif_lain' => null,
            'penghasilan_komprehensif_lain_setelah_pajak' => null,
            'penghasilan_komprehensif_tahun_berjalan' => null,
            'arus_kas_operasi' => null,
            'arus_kas_pendanaan' => null,
            'kas_awal_tahun' => null,
            'kas_akhir_tahun' => null,
            'portofolio_efek' => null,
            'instrumen_pasar_uang' => null,
            'piutang_transaksi_efek' => null,
            'piutang_bunga_dan_dividen' => null,
            'uang_muka_diterima' => null,
            'liabilitas_pembelian_kembali' => null,
            'beban_akrual' => null,
            'liabilitas_atas_biaya' => null,
            'pembelian_kembali_unit_penyertaan' => null,
            'utang_pajak_lainnya' => null,
            'pendapatan_investasi' => null,
            'pendapatan_lainnya' => null,
            'beban_investasi' => null,
            'beban_pengelolaan_investasi' => null,
            'pembelian_efek_ekuitas' => null,
            'penjualan_efek_ekuitas' => null,
            'penerimaan_bunga_deposito' => null,
            'penerimaan_bunga_jasa_giro' => null,
            'penerimaan_dividen_kas' => null,
            'pembayaran_jasa_pengelolaan' => null,
            'pembayaran_jasa_kustodian' => null,
            'pembayaran_beban_lain_arus' => null,
            'kas_bersih_aktivitas_operasi' => null,
            'penerimaan_penjualan_unit' => null,
            'pembayaran_pembelian_kembali_unit' => null,
            'kas_bersih_aktivitas_pendanaan' => null,
            'kenaikan_kas_setara_kas' => null,
            'total_hasil_investasi' => null,
            'hasil_investasi_setelah_biaya' => null,
            'persentase_pph' => null,
            'fair_value_level_1' => null,
            'fair_value_level_2' => null,
            'fair_value_level_3' => null,
            'unit_milik_investor' => null,
            'unit_milik_mi' => null,
            'total_unit_beredar' => null,
            'alokasi_aset' => [],
            'sektor' => [],
            'efek' => [],
            'kinerja' => [],
            'obligasi' => [],
            'sukuk' => [],
            'bank' => [],
            'pasar_uang' => [],
            'piutang_bunga_detail' => [],
        ];

        $data = array_merge($defaults, array_intersect_key($data, $defaults));

        if (is_string($data['kategori'])) {
            $data['kategori'] = array_values(array_filter(array_map('trim', preg_split('/[,;|]/', $data['kategori']))));
        }
        if (!is_array($data['kategori'])) {
            $data['kategori'] = [];
        }

        foreach (['alokasi_aset', 'sektor', 'efek', 'kinerja', 'obligasi', 'sukuk', 'bank', 'pasar_uang', 'piutang_bunga_detail'] as $field) {
            $data[$field] = is_array($data[$field]) ? array_values($data[$field]) : [];
        }

        if (!empty($data['tanggal_data']) && (empty($data['ffs_bulan']) || empty($data['ffs_tahun']))) {
            try {
                $date = \Carbon\Carbon::parse($data['tanggal_data']);
                $data['ffs_bulan'] = $data['ffs_bulan'] ?: $date->month;
                $data['ffs_tahun'] = $data['ffs_tahun'] ?: $date->year;
            } catch (\Throwable) {
                //
            }
        }

        $data['alokasi_aset'] = array_map(function ($row) {
            if (!is_array($row)) {
                return ['nama_aset' => (string) $row, 'persentase' => null];
            }
            if (isset($row['kategori']) && empty($row['nama_aset'])) {
                $row['nama_aset'] = $row['kategori'];
            }
            if (isset($row['nama']) && empty($row['nama_aset'])) {
                $row['nama_aset'] = $row['nama'];
            }
            if (isset($row['bobot']) && !isset($row['persentase'])) {
                $row['persentase'] = $row['bobot'];
            }
            return $row;
        }, $data['alokasi_aset']);

        $data['sektor'] = array_map(function ($row) {
            if (!is_array($row)) {
                return ['nama_sektor' => (string) $row, 'bobot' => null];
            }
            if (isset($row['kategori']) && empty($row['nama_sektor'])) {
                $row['nama_sektor'] = $row['kategori'];
            }
            if (isset($row['persentase']) && !isset($row['bobot'])) {
                $row['bobot'] = $row['persentase'];
            }
            return $row;
        }, $data['sektor']);

        $data['efek'] = array_map(function ($row) {
            if (!is_array($row)) {
                return [];
            }
            foreach ([
                'ticker' => 'kode_efek',
                'kode_saham' => 'kode_efek',
                'nama_saham' => 'nama_efek',
                'kontribusi_ihsg' => 'kontribusi_kinerja',
                'kontribusi' => 'kontribusi_kinerja',
                'kapitalisasi_pasar' => 'market_cap',
                'nilai_pasar_efek' => 'nilai_pasar',
                'nilai_investasi' => 'nilai_pasar',
                'harga_perolehan_efek' => 'harga_perolehan',
                'persen_terhadap_nab' => 'persen_nab',
                'persentase_nab' => 'persen_nab',
                'pct_nab' => 'persen_nab',
                'number_of_shares' => 'jumlah_lembar',
                'shares' => 'jumlah_lembar',
                'average_cost' => 'harga_perolehan_rata_rata',
            ] as $alias => $target) {
                if (isset($row[$alias]) && !isset($row[$target])) {
                    $row[$target] = $row[$alias];
                }
            }
            return $row;
        }, $data['efek']);

        return $data;
    }

    private function safeExtract(string $method, array $args): mixed
    {
        try {
            return $this->$method(...$args);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function extractNamaReksaDana(array $lines, string $fullText): ?string
    {
        foreach ($lines as $i => $line) {
            $lower = strtolower($line);
            if (preg_match('/(reksa\s+dana|fund)/i', $lower) && $i < 15) {
                $clean = preg_replace('/^(fund\s*fact\s*sheet|reksa\s*dana|fact\s*sheet|fund)\s*/i', '', $line);
                $clean = trim($clean, " :\t\r\n");
                if (strlen($clean) > 5) return $clean;
            }
        }

        if (preg_match('/nama\s+(?:produk|reksa\s*dana|fund)\s*[:\-]?\s*(.+)/i', $fullText, $m)) {
            $name = trim($m[1]);
            if (strlen($name) > 3) return $name;
        }

        foreach ($lines as $line) {
            if (preg_match('/reksa\s+dana\s+([A-Za-z\s().,&]+)/i', $line, $m)) {
                $name = trim($m[1]);
                $name = preg_replace('/\s+/', ' ', $name);
                if (strlen($name) > 5) return $name;
            }
        }

        return null;
    }

    private function extractJenisReksaDana(array $lines, string $fullText): ?string
    {
        $jenisMapping = [
            'Saham' => ['saham', 'ekuitas', 'equity', 'ekuiti'],
            'Pendapatan Tetap' => ['pendapatan tetap', 'fixed income', 'fixedincome', 'obligasi'],
            'Campuran' => ['campuran', 'balance', 'balanced', 'campur'],
            'Pasar Uang' => ['pasar uang', 'money market', 'money mmarket'],
        ];

        foreach ($lines as $line) {
            if (preg_match('/(?:jenis|kategori|tipe|type)\s*[:\-]?\s*(.+)/i', $line, $m)) {
                $value = strtolower(trim($m[1]));
                foreach ($jenisMapping as $jenis => $keywords) {
                    if ($this->matchKeyword($value, $keywords)) return $jenis;
                }
            }
        }

        foreach ($jenisMapping as $jenis => $keywords) {
            if ($this->matchKeyword($fullText, $keywords)) return $jenis;
        }

        return null;
    }

    private function matchKeyword(string $haystack, array $keywords): bool
    {
        foreach ($keywords as $keyword) {
            if (str_contains($haystack, $keyword)) {
                return true;
            }
        }
        return false;
    }

    private function extractAum(array $lines, string $fullText): ?float
    {
        $aumLabels = ['nilai aktiva bersih', 'nab', 'total aum', 'aum', 'dana kelolaan', 'total nav', 'nav'];

        foreach ($lines as $line) {
            $lower = strtolower($line);

            $isAumLine = false;
            foreach ($aumLabels as $label) {
                if (str_contains($lower, $label)) { $isAumLine = true; break; }
            }

            if ($isAumLine) {
                if (preg_match('/(?:rp\.?\s*)?([\d.]+(?:[,\d]+)?)\s*(?:miliar|milyar|m|triliun|t|juta)/i', $line, $m)) {
                    $value = str_replace(['.', ','], ['', '.'], $m[1]);
                    $val = (float) $value;

                    if ($val > 0) {
                        if (stripos($m[0], 'triliun') !== false) return $val * 1000000000000;
                        if (stripos($m[0], 'miliar') !== false || stripos($m[0], 'milyar') !== false) return $val * 1000000000;
                        if (stripos($m[0], 'juta') !== false) return $val * 1000000;
                        return $val;
                    }
                }

                if (preg_match('/(?:rp\.?\s*)?([\d.]+)/i', $line, $m)) {
                    $value = str_replace(['.', ','], ['', '.'], $m[1]);
                    $val = (float) $value;
                    if ($val > 1000000000) return $val;
                }
            }
        }

        return null;
    }

    private function extractTotalMarcap(array $lines, string $fullText): ?float
    {
        $labels = ['market cap', 'marcap', 'kapitalisasi pasar', 'total kapitalisasi'];

        foreach ($lines as $line) {
            $lower = strtolower($line);
            foreach ($labels as $label) {
                if (str_contains($lower, $label) && preg_match('/(?:rp\.?\s*)?([\d.]+(?:[,\d]+)?)\s*(?:miliar|milyar|m|triliun|t|juta)?/i', $line, $m)) {
                    $value = str_replace(['.', ','], ['', '.'], $m[1]);
                    $val = (float) $value;
                    if ($val > 0) {
                        if (stripos($line, 'triliun') !== false) return $val * 1000000000000;
                        if (stripos($line, 'miliar') !== false) return $val * 1000000000;
                        if ($val > 1000000000) return $val;
                        return $val * 1000000000;
                    }
                }
            }
        }

        return null;
    }

    private function extractSektor(array $lines, string $fullText): array
    {
        $sektorData = [];
        $inSektor = false;

        $sektorStart = ['komposisi sektor', 'alokasi sektor', 'sektor alokasi', 'sector allocation',
                         'alokasi berdasarkan sektor', 'sektor composition', 'alokasi sektor ekonomi'];

        foreach ($lines as $i => $line) {
            $lower = strtolower($line);

            foreach ($sektorStart as $keyword) {
                if (str_contains($lower, $keyword)) {
                    $inSektor = true;
                    continue 2;
                }
            }

            if ($inSektor) {
                $sectionEnd = ['total', 'jumlah', 'efek', 'obligasi', 'kinerja',
                               'portofolio', 'bank', 'kas'];

                $isEnd = false;
                foreach ($sectionEnd as $end) {
                    if (str_contains($lower, $end) && count($sektorData) > 0) {
                        $isEnd = true;
                        break;
                    }
                }

                if ($isEnd) { $inSektor = false; continue; }

                if (preg_match('/^([A-Za-z\s&\/]+?)\s+([\d.,]+)\s*%?$/', $line, $m)) {
                    $nama = trim($m[1]);
                    $bobot = (float) str_replace(',', '.', $m[2]);
                    if ($bobot > 0 && $bobot <= 100 && strlen($nama) > 2) {
                        $sektorData[] = ['nama_sektor' => $nama, 'bobot' => $bobot];
                    }
                } elseif (preg_match('/^([A-Za-z\s&\/]+?)\s+([\d.,]+)/', $line, $m)) {
                    $nama = trim($m[1]);
                    $bobot = (float) str_replace(',', '.', $m[2]);
                    if ($bobot > 0 && $bobot <= 100 && strlen($nama) > 2) {
                        $sektorData[] = ['nama_sektor' => $nama, 'bobot' => $bobot];
                    }
                }
            }
        }

        // Fallback sederhana, lebih ketat
        if (empty($sektorData)) {
            $knownSectors = ['keuangan','financial','energi','energy','infrastruktur','infrastructure',
                             'konsumsi','consumer','teknologi','technology','properti','property',
                             'industri','industrial','kesehatan','healthcare',
                             'transportasi','transportation','perdagangan','trade',
                             'utilitas','utility','material','kas','cash','lainnya','other'];

            foreach ($lines as $line) {
                $lower = strtolower($line);
                foreach ($knownSectors as $sector) {
                    if ($lower === $sector || preg_match('/^' . preg_quote($sector, '/') . '\b/i', $lower)) {
                        if (preg_match('/([\d.,]+)\s*%?\s*$/', $line, $m)) {
                            $bobot = (float) str_replace(',', '.', $m[1]);
                            if ($bobot > 0 && $bobot <= 100) {
                                $nama = ucfirst(trim(preg_replace('/^(' . preg_quote($sector, '/') . ')\s*/i', '', $line)));
                                $nama = $nama ?: ucfirst($sector);
                                $nama = trim(preg_replace('/\s+\d[\d.,\s]*$/', '', $nama));
                                $sektorData[] = ['nama_sektor' => $nama ?: ucfirst($sector), 'bobot' => $bobot];
                                break;
                            }
                        }
                    }
                }
            }
        }

        return $sektorData;
    }

    private function extractAlokasiAset(array $lines, string $fullText): array
    {
        $rows = [];
        $started = false;

        $startKeywords = [
            'alokasi aset', 'asset allocation', 'komposisi aset', 'alokasi portofolio',
            'komposisi portofolio', 'alokasi investasi', 'alokasi kelas aset', 'asset class',
        ];
        $stopKeywords = ['sektor', 'komposisi sektor', 'daftar efek', 'portofolio efek',
            'ten largest', '10 efek', 'top 10', 'laporan', 'catatan', 'neraca',
            'estimated', 'kinerja', 'imbal hasil', 'pengukuran', 'perubahan unit',
            'neraca komparatif', 'laporan arus kas', 'detail', 'informasi'];

        $knownAssets = [
            'saham' => ['saham', 'equity', 'saham? (idx)'],
            'obligasi' => ['obligasi', 'bond', 'efek utang', 'fixed income'],
            'sukuk' => ['sukuk', 'sbsn'],
            'pasar uang' => ['pasar uang', 'money market', 'instrumen pasar uang'],
            'kas' => ['kas', 'cash', 'kas dan setara', 'kas & bank', 'kas di bank'],
            'deposito' => ['deposito', 'deposito berjangka', 'time deposit'],
            'lainnya' => ['lainnya', 'others', 'lain-lain'],
        ];

        $normalizeName = function (string $name) use ($knownAssets): string {
            $name = strtolower(trim($name));
            foreach ($knownAssets as $canonical => $aliases) {
                foreach ($aliases as $alias) {
                    if ($name === $alias || str_contains($name, $alias)) {
                        return ucfirst($canonical);
                    }
                }
            }
            return trim($name);
        };

        foreach ($lines as $line) {
            $lower = strtolower($line);

            if (!$started) {
                foreach ($startKeywords as $kw) {
                    if (str_contains($lower, $kw) && strlen($lower) < 80) {
                        $started = true;
                        continue 2;
                    }
                }
                continue;
            }

            if (count($rows) > 0) {
                foreach ($stopKeywords as $end) {
                    if (str_contains($lower, $end) && strlen($lower) < 80) {
                        $started = false;
                        continue 2;
                    }
                }
            }

            // Format: "Saham 65.2%" atau "Saham 65.2 1500000000000"
            if (preg_match('/^([A-Za-z][A-Za-z\s&\/]+?)\s+([\d.,]+)\s*%?\s*([\d.,]*)\s*$/', $line, $m)) {
                $nama = trim($m[1]);
                $persen = (float) str_replace(',', '.', $m[2]);
                if ($persen > 0 && $persen <= 100 && strlen($nama) >= 3) {
                    $canonical = $normalizeName($nama);
                    if ($canonical !== '') {
                        $rows[] = ['nama_aset' => $canonical, 'persentase' => $persen];
                    }
                }
                continue;
            }

            // Format: "Saham 65,2 25%"
            if (preg_match('/^([A-Za-z][A-Za-z\s&\/]+?)\s+([\d.,]+)\s+([\d.,]+)\s*%?\s*$/', $line, $m)) {
                $nama = trim($m[1]);
                $persen = (float) str_replace(',', '.', $m[3]);
                if ($persen > 0 && $persen <= 100 && strlen($nama) >= 3) {
                    $canonical = $normalizeName($nama);
                    if ($canonical !== '') {
                        $rows[] = ['nama_aset' => $canonical, 'persentase' => $persen];
                    }
                }
                continue;
            }
        }

        // Dedup by canonical name — sum persentase untuk nama yg sama
        $aggregated = [];
        foreach ($rows as $r) {
            $key = strtolower($r['nama_aset']);
            if (!isset($aggregated[$key])) {
                $aggregated[$key] = ['nama_aset' => $r['nama_aset'], 'persentase' => 0.0];
            }
            $aggregated[$key]['persentase'] += $r['persentase'];
        }

        return array_values($aggregated);
    }

    private function extractEfek(array $lines, string $fullText): array
    {
        $efekData = [];
        $inEfek = false;

        $efekStart = ['portofolio', '10 efek', 'top 10', '10 besar', 'komposisi efek',
                       'efek terbesar', 'daftar efek', 'holding', 'saham terbesar',
                       'equity portfolio', 'securities', 'top holding', '10 saham'];

        foreach ($lines as $i => $line) {
            $lower = strtolower($line);

            foreach ($efekStart as $keyword) {
                if (str_contains($lower, $keyword) && strlen($lower) < 80) {
                    $inEfek = true;
                    continue 2;
                }
            }

            if ($inEfek) {
                if (count($efekData) > 0 && preg_match('/^(obligasi|sukuk|kinerja|bank|total|jumlah|catatan|laporan)/i', $lower)) break;

                $efek = $this->parseEfekLine($line);
                if ($efek) {
                    $efekData[] = $efek;
                    continue;
                }

                if (count($efekData) >= 100) break;
            }
        }

        if (empty($efekData)) {
            foreach ($lines as $line) {
                $efek = $this->parseEfekLine($line);
                if ($efek) {
                    $efekData[] = $efek;
                    if (count($efekData) >= 100) break;
                }
            }
        }

        return $efekData;
    }

    private function parseEfekLine(string $line): ?array
    {
        if (preg_match('/^([A-Z][A-Z0-9]{1,5})\s+(.+?)\s+([\d.,]+)\s*%/', $line, $m)) {
            $bobot = (float) str_replace(',', '.', $m[3]);
            if ($bobot > 0 && $bobot <= 100 && strlen(trim($m[2])) > 1) {
                $rest = trim(substr($line, strpos($line, $m[3]) + strlen($m[3])));
                $extraNumbers = [];
                if (preg_match_all('/([\d][\d.,]*)/', $rest, $extra)) {
                    $extraNumbers = $extra[1];
                }
                $row = [
                    'kode_efek' => $m[1],
                    'nama_efek' => trim($m[2]),
                    'bobot' => $bobot,
                    'harga' => !empty($extraNumbers[0]) ? (float) str_replace(',', '.', $extraNumbers[0]) : null,
                    'sektor' => '',
                    'kontribusi_kinerja' => null,
                    'market_cap' => null,
                    'nilai_pasar' => null,
                    'harga_perolehan' => null,
                    'persen_nab' => null,
                    'top_10' => false,
                ];
                // Heuristik konservatif: jika setelah bobot ada angka tambahan,
                // anggap angka pertama = harga_perolehan (atau nilai_pasar) dan
                // angka terakhir yang diikuti tanda % = persen_nab.
                if (!empty($extraNumbers) && preg_match('/([\d.,]+)\s*%\s*$/', $rest, $pnab)) {
                    $row['persen_nab'] = (float) str_replace(',', '.', $pnab[1]);
                }
                if (!empty($extraNumbers[0])) {
                    if ($row['persen_nab'] !== null && (float) str_replace(',', '.', $extraNumbers[0]) !== $row['persen_nab']) {
                        $row['harga_perolehan'] = (float) str_replace(',', '.', $extraNumbers[0]);
                    } elseif ($row['persen_nab'] === null) {
                        // Tidak ada % → anggap angka pertama = harga_perolehan
                        $row['harga_perolehan'] = (float) str_replace(',', '.', $extraNumbers[0]);
                    }
                }
                return $row;
            }
        }

        if (preg_match('/^([A-Z][A-Z0-9]{1,5})\s+(.+?)\s+([\d.,]+)\s*$/', $line, $m)) {
            $bobot = (float) str_replace(',', '.', $m[3]);
            if ($bobot > 0 && $bobot <= 100 && strlen(trim($m[2])) > 1) {
                return [
                    'kode_efek' => $m[1],
                    'nama_efek' => trim($m[2]),
                    'bobot' => $bobot,
                    'harga' => null,
                    'sektor' => '',
                    'kontribusi_kinerja' => null,
                    'market_cap' => null,
                    'nilai_pasar' => null,
                    'harga_perolehan' => null,
                    'persen_nab' => null,
                    'top_10' => false,
                ];
            }
        }

        if (preg_match('/^([A-Za-z][A-Za-z\s,.]{3,}?)\s*\(([A-Z][A-Z0-9]{1,5})\)\s+([\d.,]+)\s*%?/', $line, $m)) {
            $bobot = (float) str_replace(',', '.', $m[3]);
            if ($bobot > 0 && $bobot <= 100) {
                return [
                    'kode_efek' => $m[2],
                    'nama_efek' => trim($m[1]),
                    'bobot' => $bobot,
                    'harga' => null,
                    'sektor' => '',
                    'kontribusi_kinerja' => null,
                    'market_cap' => null,
                    'nilai_pasar' => null,
                    'harga_perolehan' => null,
                    'persen_nab' => null,
                    'top_10' => false,
                ];
            }
        }

        if (preg_match('/^\s*\d+\.?\s+([A-Z][A-Z0-9]{1,5})\s+(.+?)\s+([\d.,]+)\s*%?/', $line, $m)) {
            $bobot = (float) str_replace(',', '.', $m[3]);
            if ($bobot > 0 && $bobot <= 100 && strlen(trim($m[2])) > 1) {
                return [
                    'kode_efek' => $m[1],
                    'nama_efek' => trim($m[2]),
                    'bobot' => $bobot,
                    'harga' => null,
                    'sektor' => '',
                    'kontribusi_kinerja' => null,
                    'market_cap' => null,
                    'nilai_pasar' => null,
                    'harga_perolehan' => null,
                    'persen_nab' => null,
                    'top_10' => false,
                ];
            }
        }

        return null;
    }

    private function extractKinerja(array $lines, string $fullText): array
    {
        $kinerjaData = [];
        $inKinerja = false;

        $kinerjaStart = ['kinerja', 'imbal hasil', 'return', 'performance', 'bulanan',
                         'bulan berjalan', 'monthly', 'hasil investasi'];

        foreach ($lines as $i => $line) {
            $lower = strtolower($line);

            foreach ($kinerjaStart as $keyword) {
                if (str_contains($lower, $keyword) && strlen($lower) < 60) {
                    $inKinerja = true;
                    continue 2;
                }
            }

            if ($inKinerja) {
                if (preg_match('/^(obligasi|sektor|efek|bank)/i', $lower) && count($kinerjaData) > 0) break;

                if (preg_match('/(\d{4}[-]?\d{2}|\w+\s+\d{4}|\w+[-]?\d{2}|\d{2}[-]?\d{4})\s+([\-+]?[\d.,]+)\s*%?/', $line, $m)) {
                    $kinerjaData[] = [
                        'periode' => $this->normalizePeriode($m[1]),
                        'return_pct' => (float) str_replace(',', '.', $m[2]),
                    ];
                }
            }
        }

        return $kinerjaData;
    }

    private function normalizePeriode(string $periode): string
    {
        $months = [
            'januari'=>'01','jan'=>'01','january'=>'01',
            'februari'=>'02','feb'=>'02','february'=>'02',
            'maret'=>'03','mar'=>'03','march'=>'03',
            'april'=>'04','apr'=>'04',
            'mei'=>'05','may'=>'05',
            'juni'=>'06','jun'=>'06','june'=>'06',
            'juli'=>'07','jul'=>'07','july'=>'07',
            'agustus'=>'08','agu'=>'08','aug'=>'08','august'=>'08',
            'september'=>'09','sep'=>'09','sept'=>'09',
            'oktober'=>'10','okt'=>'10','oct'=>'10','october'=>'10',
            'november'=>'11','nov'=>'11','nop'=>'11','nopember'=>'11',
            'desember'=>'12','des'=>'12','dec'=>'12','december'=>'12',
        ];

        if (preg_match('/^(\d{4})-(\d{2})/', $periode)) return substr($periode, 0, 7);
        if (preg_match('/^(\d{2})-(\d{4})$/', $periode, $m)) return $m[2] . '-' . $m[1];

        foreach ($months as $name => $num) {
            if (preg_match('/\b' . $name . '\b\s*[-]?\s*(\d{2,4})/i', $periode, $m)) {
                $year = $m[1];
                if (strlen($year) === 2) $year = '20' . $year;
                return $year . '-' . $num;
            }
        }

        return $periode;
    }

    private function extractObligasi(array $lines, string $fullText): array
    {
        $obligasiData = [];
        $inObligasi = false;

        $obligasiStart = ['obligasi', 'bond', 'fixed income', 'surat utang', 'daftar obligasi',
                          'komposisi obligasi', 'portofolio obligasi', 'obligasi terbesar'];

        foreach ($lines as $i => $line) {
            $lower = strtolower($line);

            foreach ($obligasiStart as $keyword) {
                if (str_contains($lower, $keyword) && strlen($lower) < 80) {
                    $inObligasi = true;
                    continue 2;
                }
            }

            if ($inObligasi) {
                if (count($obligasiData) > 0 && preg_match('/^(kinerja|sektor|efek|bank|total|jumlah|sukuk|catatan)/i', $lower)) break;

                $ob = $this->parseObligasiLine($line);
                if ($ob) {
                    $obligasiData[] = $ob;
                    continue;
                }

                if (count($obligasiData) >= 100) break;
            }
        }

        if (empty($obligasiData)) {
            foreach ($lines as $line) {
                $ob = $this->parseObligasiLine($line);
                if ($ob) {
                    $obligasiData[] = $ob;
                    if (count($obligasiData) >= 100) break;
                }
            }
        }

        return $obligasiData;
    }

    private function parseObligasiLine(string $line): ?array
    {
        if (preg_match('/^((?:FR|INDON|SUN|ORI|SR|PBS|SPN|ST|SBR)[A-Z0-9]*)\s+(.+?)\s+([\d.,]+)\s+([\d.,]+)\s+([A-Z][A-Z+\-]*)/i', $line, $m)) {
            return [
                'kode_obligasi' => strtoupper($m[1]),
                'nama_obligasi' => trim($m[2]),
                'bobot' => (float) str_replace(',', '.', $m[3]),
                'durasi' => (float) str_replace(',', '.', $m[4]),
                'rating' => $m[5],
            ];
        }

        if (preg_match('/^((?:FR|INDON|SUN|ORI|SR|PBS|SPN|ST|SBR)[A-Z0-9]*)\s+(.+?)\s+([\d.,]+)\s*%?/', $line, $m)) {
            $bobot = (float) str_replace(',', '.', $m[3]);
            if ($bobot > 0 && $bobot <= 100 && strlen(trim($m[2])) > 1) {
                return [
                    'kode_obligasi' => strtoupper($m[1]),
                    'nama_obligasi' => trim($m[2]),
                    'bobot' => $bobot,
                    'durasi' => null,
                    'rating' => null,
                ];
            }
        }

        if (preg_match('/^\s*\d+\.?\s+((?:FR|INDON|SUN|ORI|SR|PBS|SPN|ST|SBR)[A-Z0-9]*)\s+(.+?)\s+([\d.,]+)\s*%?/i', $line, $m)) {
            $bobot = (float) str_replace(',', '.', $m[3]);
            if ($bobot > 0 && $bobot <= 100 && strlen(trim($m[2])) > 1) {
                return [
                    'kode_obligasi' => strtoupper($m[1]),
                    'nama_obligasi' => trim($m[2]),
                    'bobot' => $bobot,
                    'durasi' => null,
                    'rating' => null,
                ];
            }
        }

        return null;
    }

    private function extractSukuk(array $lines, string $fullText): array
    {
        $sukukData = [];
        $inSukuk = false;

        $sukukStart = ['sukuk', 'sbsn', 'surat berharga syariah', 'daftar sukuk',
                       'komposisi sukuk', 'portofolio sukuk', 'sukuk ritel',
                       'sukuk negara', 'sukuk korporasi'];

        foreach ($lines as $i => $line) {
            $lower = strtolower($line);

            foreach ($sukukStart as $keyword) {
                if (str_contains($lower, $keyword) && strlen($lower) < 80) {
                    $inSukuk = true;
                    continue 2;
                }
            }

            if ($inSukuk) {
                if (count($sukukData) > 0 && preg_match('/^(kinerja|sektor|efek|bank|total|jumlah|obligasi|catatan)/i', $lower)) break;

                $sk = $this->parseSukukLine($line);
                if ($sk) {
                    $sukukData[] = $sk;
                    continue;
                }

                if (count($sukukData) >= 100) break;
            }
        }

        if (empty($sukukData)) {
            foreach ($lines as $line) {
                $sk = $this->parseSukukLine($line);
                if ($sk) {
                    $sukukData[] = $sk;
                    if (count($sukukData) >= 100) break;
                }
            }
        }

        return $sukukData;
    }

    private function parseSukukLine(string $line): ?array
    {
        if (preg_match('/^((?:SR|PBS|ST|SBR|CWGR|SUKUK)[A-Z0-9]*)\s+(.+?)\s+([\d.,]+)\s*%?/', $line, $m)) {
            $bobot = (float) str_replace(',', '.', $m[3]);
            if ($bobot > 0 && $bobot <= 100 && strlen(trim($m[2])) > 1) {
                $kode = strtoupper($m[1]);
                $jenis = str_starts_with($kode, 'PBS') || str_starts_with($kode, 'SR') || str_starts_with($kode, 'ST') || str_starts_with($kode, 'SBR')
                    ? 'Negara' : null;
                return [
                    'kode_sukuk'  => $kode,
                    'nama_sukuk'  => trim($m[2]),
                    'bobot'       => $bobot,
                    'yield'       => null,
                    'jatuh_tempo' => null,
                    'rating'      => null,
                    'jenis_sukuk' => $jenis,
                ];
            }
        }

        if (preg_match('/^\s*\d+\.?\s+((?:SR|PBS|ST|SBR|CWGR|SUKUK)[A-Z0-9]*)\s+(.+?)\s+([\d.,]+)\s*%?/i', $line, $m)) {
            $bobot = (float) str_replace(',', '.', $m[3]);
            if ($bobot > 0 && $bobot <= 100 && strlen(trim($m[2])) > 1) {
                $kode = strtoupper($m[1]);
                return [
                    'kode_sukuk'  => $kode,
                    'nama_sukuk'  => trim($m[2]),
                    'bobot'       => $bobot,
                    'yield'       => null,
                    'jatuh_tempo' => null,
                    'rating'      => null,
                    'jenis_sukuk' => str_starts_with($kode, 'PBS') || str_starts_with($kode, 'SR') || str_starts_with($kode, 'ST') ? 'Negara' : null,
                ];
            }
        }

        $lower = strtolower($line);
        if (str_contains($lower, 'sukuk') && preg_match('/^(.+?)\s+([\d.,]+)\s*%/', $line, $m)) {
            $bobot = (float) str_replace(',', '.', $m[2]);
            if ($bobot > 0 && $bobot <= 100 && strlen(trim($m[1])) > 3) {
                return [
                    'kode_sukuk'  => '',
                    'nama_sukuk'  => trim($m[1]),
                    'bobot'       => $bobot,
                    'yield'       => null,
                    'jatuh_tempo' => null,
                    'rating'      => null,
                    'jenis_sukuk' => null,
                ];
            }
        }

        return null;
    }

    private function extractBank(array $lines, string $fullText): array
    {
        $bankData = [];
        $inBank = false;

        $bankStart = ['bank', 'deposito', 'deposito berjangka', 'kas di bank',
                      'komposisi bank', 'daftar bank', 'cash in bank'];

        foreach ($lines as $i => $line) {
            $lower = strtolower($line);

            foreach ($bankStart as $keyword) {
                if (str_contains($lower, $keyword) && strlen($lower) < 80 && !str_contains($lower, 'obligasi') && !str_contains($lower, 'sektor')) {
                    $inBank = true;
                    continue 2;
                }
            }

            if ($inBank) {
                if (count($bankData) > 0 && preg_match('/^(kinerja|sektor|efek|obligasi|total|jumlah|catatan)/i', $lower)) break;

                $bk = $this->parseBankLine($line);
                if ($bk) {
                    $bankData[] = $bk;
                    continue;
                }

                if (count($bankData) >= 20) break;
            }
        }

        if (empty($bankData)) {
            foreach ($lines as $line) {
                $bk = $this->parseBankLine($line);
                if ($bk) {
                    $bankData[] = $bk;
                    if (count($bankData) >= 20) break;
                }
            }
        }

        return $bankData;
    }

    private function parseBankLine(string $line): ?array
    {
        if (!preg_match('/bank/i', $line) && !preg_match('/deposito/i', $line)) return null;

        if (preg_match('/(?:^|\b)(bank\s+\S+(?:\s+\S+){0,5}?)\s+([\d.,]+)\s*%?/', $line, $m)) {
            $nama = trim($m[1]);
            $bobot = (float) str_replace(',', '.', $m[2]);
            if ($bobot > 0 && $bobot <= 100 && strlen($nama) > 3) {
                $rest = trim(substr($line, strpos($line, $m[2]) + strlen($m[2])));
                $extras = [];
                if (preg_match_all('/([\d.,]+)/', $rest, $extra)) {
                    $extras = $extra[1];
                }
                return [
                    'nama_bank' => $nama,
                    'bobot' => $bobot,
                    'car' => !empty($extras[0]) ? (float) str_replace(',', '.', $extras[0]) : null,
                    'npl' => !empty($extras[1]) ? (float) str_replace(',', '.', $extras[1]) : null,
                    'klasifikasi_risiko' => null,
                ];
            }
        }

        return null;
    }

    private function extractManajerInvestasi(array $lines, string $fullText): ?string
    {
        foreach ($lines as $i => $line) {
            if (preg_match('/(?:manajer\s+investasi|investment\s+manager|dikelola\s+oleh|manager)\s*[:\-]?\s*(.+)/i', $line, $m)) {
                $name = trim($m[1]);
                $name = preg_replace('/^(PT\.?\s*)/i', 'PT ', $name);
                if (strlen($name) > 3 && strlen($name) < 100) return $name;
            }
        }
        return null;
    }

    private function extractBankKustodian(array $lines, string $fullText): ?string
    {
        foreach ($lines as $line) {
            if (preg_match('/(?:bank\s+kustodian|kustodian|custodian\s*bank|custodian)\s*[:\-]?\s*(.+)/i', $line, $m)) {
                $name = trim($m[1]);
                if (strlen($name) > 3 && strlen($name) < 100) return $name;
            }
        }
        return null;
    }

    private function extractTanggalPeluncuran(array $lines, string $fullText): ?string
    {
        foreach ($lines as $line) {
            if (preg_match('/(?:tanggal\s+peluncuran|inception\s+date|launch\s+date|tanggal\s+pendirian)\s*[:\-]?\s*(.+)/i', $line, $m)) {
                $dateStr = trim($m[1]);
                try {
                    $date = \Carbon\Carbon::parse($dateStr);
                    return $date->format('Y-m-d');
                } catch (\Throwable) {
                    if (preg_match('/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})/', $dateStr, $dm)) {
                        return $dm[3] . '-' . str_pad($dm[2], 2, '0', STR_PAD_LEFT) . '-' . str_pad($dm[1], 2, '0', STR_PAD_LEFT);
                    }
                }
            }
        }
        return null;
    }

    private function extractMataUang(array $lines, string $fullText): ?string
    {
        foreach ($lines as $line) {
            if (preg_match('/(?:mata\s+uang|currency)\s*[:\-]?\s*(.+)/i', $line, $m)) {
                $val = strtoupper(trim($m[1]));
                if (preg_match('/\b(IDR|USD|SGD|EUR|JPY|GBP|AUD|MYR|CNY)\b/', $val, $cm)) {
                    return $cm[1];
                }
            }
        }
        if (preg_match('/(?:mata\s+uang|currency)\s*[:\-]?\s*(?:Rupiah|IDR)/i', $fullText)) return 'IDR';
        if (preg_match('/(?:mata\s+uang|currency)\s*[:\-]?\s*(?:Dollar|USD)/i', $fullText)) return 'USD';
        return null;
    }

    private function extractBenchmark(array $lines, string $fullText): ?string
    {
        foreach ($lines as $line) {
            if (preg_match('/(?:benchmark|index\s+acuan|acuan|indeks\s+acuan)\s*[:\-]?\s*(.+)/i', $line, $m)) {
                $val = trim($m[1]);
                if (strlen($val) > 2 && strlen($val) < 150) return $val;
            }
        }
        return null;
    }

    private function extractTujuanInvestasi(array $lines, string $fullText): ?string
    {
        foreach ($lines as $i => $line) {
            if (preg_match('/(?:tujuan\s+investasi|investment\s+objective)\s*[:\-]?\s*(.+)/i', $line, $m)) {
                $text = trim($m[1]);
                if (strlen($text) > 5) return mb_substr($text, 0, 500);
                if (isset($lines[$i + 1]) && strlen(trim($lines[$i + 1])) > 5) {
                    return mb_substr(trim($lines[$i + 1]), 0, 500);
                }
            }
        }
        return null;
    }

    private function extractKebijakanInvestasi(array $lines, string $fullText): ?string
    {
        foreach ($lines as $i => $line) {
            if (preg_match('/(?:kebijakan\s+investasi|investment\s+policy)\s*[:\-]?\s*(.+)/i', $line, $m)) {
                $text = trim($m[1]);
                if (strlen($text) > 5) return mb_substr($text, 0, 500);
                if (isset($lines[$i + 1]) && strlen(trim($lines[$i + 1])) > 5) {
                    return mb_substr(trim($lines[$i + 1]), 0, 500);
                }
            }
        }
        return null;
    }

    private function extractNabPerUnit(array $lines, string $fullText): ?float
    {
        foreach ($lines as $line) {
            if (preg_match('/(?:NAB\s*\/\s*UP|NAV\s*\/\s*Unit|NAB\s+per\s+Unit|nilai\s+aktiva\s+bersih\s+per\s+unit)\s*[:\-]?\s*(?:Rp\.?\s*)?([\d.,]+)/i', $line, $m)) {
                $value = str_replace(['.', ','], ['', '.'], $m[1]);
                $val = (float) $value;
                if ($val > 0) return $val;
            }
        }
        return null;
    }

    private function extractUnitPenyertaan(array $lines, string $fullText): ?float
    {
        foreach ($lines as $line) {
            if (preg_match('/(?:unit\s+penyertaan|units\s+outstanding|jumlah\s+unit)\s*[:\-]?\s*([\d.,]+)/i', $line, $m)) {
                $value = str_replace(['.', ','], ['', '.'], $m[1]);
                $val = (float) $value;
                if ($val > 0) return $val;
            }
        }
        return null;
    }

    private function extractTanggalData(array $lines, string $fullText): ?string
    {
        foreach ($lines as $line) {
            if (preg_match('/(?:periode|as\s+at|per\s+tanggal|tanggal\s+data|data\s+per)\s*[:\-]?\s*(.+)/i', $line, $m)) {
                $dateStr = trim($m[1]);
                try {
                    $date = \Carbon\Carbon::parse($dateStr);
                    return $date->format('Y-m-d');
                } catch (\Throwable) {
                    if (preg_match('/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})/', $dateStr, $dm)) {
                        return $dm[3] . '-' . str_pad($dm[2], 2, '0', STR_PAD_LEFT) . '-' . str_pad($dm[1], 2, '0', STR_PAD_LEFT);
                    }
                }
            }
        }
        return null;
    }

    private function extractReturnYtd(array $lines, string $fullText): ?float
    {
        foreach ($lines as $line) {
            if (preg_match('/(?:return\s+YTD|YTD|imbal\s+hasil\s+tahun\s+berjalan|return\s+tahun\s+berjalan)\s*[:\-]?\s*([\-+]?[\d.,]+)\s*%?/i', $line, $m)) {
                $val = (float) str_replace(',', '.', $m[1]);
                return $val;
            }
        }
        return null;
    }

    private function extractReturn1y(array $lines, string $fullText): ?float
    {
        foreach ($lines as $line) {
            if (preg_match('/(?:1\s+tahun|1\s*thn|1\s*yr|1\s*Y|return\s+1\s*tahun|1\s+year\s+return)\s*[:\-]?\s*([\-+]?[\d.,]+)\s*%?/i', $line, $m)) {
                $val = (float) str_replace(',', '.', $m[1]);
                return $val;
            }
        }
        return null;
    }

    private function extractManagementFee(array $lines, string $fullText): ?float
    {
        foreach ($lines as $line) {
            if (preg_match('/(?:management\s+fee|biaya\s+pengelolaan|imbal\s+jasa\s+manajer\s+investasi)\s*[:\-]?\s*([\d.,]+)\s*%?/i', $line, $m)) {
                $val = (float) str_replace(',', '.', $m[1]);
                if ($val > 0 && $val <= 100) return $val;
            }
        }
        return null;
    }

    private function extractCustodianFee(array $lines, string $fullText): ?float
    {
        foreach ($lines as $line) {
            if (preg_match('/(?:custodian\s+fee|biaya\s+kustodian|imbal\s+jasa\s+kustodian)\s*[:\-]?\s*([\d.,]+)\s*%?/i', $line, $m)) {
                $val = (float) str_replace(',', '.', $m[1]);
                if ($val > 0 && $val <= 100) return $val;
            }
        }
        return null;
    }

    private function extractLabelValue(string $line, array $labels): ?float
    {
        $lower = strtolower($line);
        foreach ($labels as $label) {
            if (str_contains($lower, strtolower($label))) {
                // 1. Angka di akhir baris (pola asli)
                if (preg_match('/([\d][\d.,]*(?:[.,]\d+)?)\s*(?:miliar|milyar|triliun|juta)?\s*$/i', $line, $m)) {
                    $value = str_replace(['.', ','], ['', '.'], $m[1]);
                    $val = (float) $value;
                    $suffix = strtolower($m[0]);
                    if (str_contains($suffix, 'triliun')) return $val * 1000000000000;
                    if (str_contains($suffix, 'miliar') || str_contains($suffix, 'milyar')) return $val * 1000000000;
                    if (str_contains($suffix, 'juta')) return $val * 1000000;
                    return $val;
                }
                // 2. Angka pertama setelah label (untuk format tabel dengan multiple kolom)
                $labelPos = stripos($lower, strtolower($label));
                $afterLabel = substr($line, $labelPos + strlen($label));
                if (preg_match('/[\s:]+(\(?\s*[\d][\d.,]*(?:[.,]\d+)?\s*\)?)\s*(?:miliar|milyar|triliun|juta)?/i', $afterLabel, $m)) {
                    $raw = trim($m[1], " \t()");
                    $isNegative = str_contains($m[1], '(') && str_contains($m[1], ')');
                    $value = str_replace(['.', ','], ['', '.'], $raw);
                    $val = (float) $value;
                    if ($isNegative) $val = -$val;
                    $suffix = strtolower($m[0]);
                    if (str_contains($suffix, 'triliun')) return $val * 1000000000000;
                    if (str_contains($suffix, 'miliar') || str_contains($suffix, 'milyar')) return $val * 1000000000;
                    if (str_contains($suffix, 'juta')) return $val * 1000000;
                    if ($val != 0) return $val;
                }
            }
        }
        return null;
    }

    private function extractLabelPercent(string $line, array $labels): ?float
    {
        $lower = strtolower($line);
        foreach ($labels as $label) {
            if (str_contains($lower, strtolower($label))) {
                // 1. Angka diikuti % sign
                if (preg_match('/([\-+]?[\d.,]+)\s*%/', $line, $m)) {
                    return (float) str_replace(',', '.', $m[1]);
                }
                // 2. Angka di akhir baris (tanpa %)
                if (preg_match('/([\-+]?[\d.,]+)\s*$/', $line, $m)) {
                    $val = (float) str_replace(',', '.', $m[1]);
                    if (abs($val) < 1000) return $val;
                }
                // 3. Angka setelah label (di tengah baris, dipisah spasi/tab)
                $labelPos = stripos($lower, strtolower($label));
                $afterLabel = substr($line, $labelPos + strlen($label));
                if (preg_match('/[\s:]+(-?[\d.,]+)/', $afterLabel, $m)) {
                    $val = (float) str_replace(',', '.', $m[1]);
                    if (abs($val) < 1000) return $val;
                }
            }
        }
        return null;
    }

    public function extractLaporanKeuangan(array $lines, string $fullText): array
    {
        $result = [];

        $fieldMap = [
            'total_aset'          => ['total aset', 'total assets', 'total aktiva', 'jumlah aset', 'jumlah aktiva'],
            'total_liabilitas'    => ['total liabilitas', 'total liabilities', 'total kewajiban', 'jumlah liabilitas', 'jumlah kewajiban'],
            'kas_dan_bank'        => ['kas dan bank', 'cash and bank', 'kas & bank', 'kas dan setara kas', 'cash and cash equivalent'],
            'piutang_bunga'       => ['piutang bunga', 'interest receivable', 'piutang atas bunga'],
            'piutang_dividen'     => ['piutang dividen', 'dividend receivable', 'piutang atas dividen'],
            'piutang_lain'        => ['piutang lain', 'other receivable', 'piutang lain-lain', 'piutang lainnya'],
            'portofolio_efek'     => ['portofolio efek', 'efek portfolio', 'portfolio of securities', 'efek bersih'],
            'instrumen_pasar_uang' => ['instrumen pasar uang', 'money market instruments', 'pasar uang bersih'],
            'piutang_transaksi_efek' => ['piutang transaksi efek', 'receivables from securities transactions', 'piutang atas transaksi efek'],
            'piutang_bunga_dan_dividen' => ['piutang bunga dan dividen', 'interest and dividend receivable', 'piutang bunga dividen'],
            'uang_muka_diterima'  => ['uang muka diterima', 'advances received', 'uang muka'],
            'liabilitas_pembelian_kembali' => ['liabilitas pembelian kembali', 'liability for repurchase', 'utang pembelian kembali'],
            'beban_akrual'        => ['beban akrual', 'accrued expenses', 'akrual'],
            'liabilitas_atas_biaya' => ['liabilitas atas biaya', 'liability for expenses'],
            'pembelian_kembali_unit_penyertaan' => ['pembelian kembali unit penyertaan', 'repurchase of units', 'unit penyertaan dibeli kembali'],
            'utang_pajak_lainnya'  => ['utang pajak lainnya', 'other tax payable', 'hutang pajak lainnya'],
            'utang_pajak'         => ['utang pajak', 'tax payable', 'hutang pajak', 'kewajiban pajak', 'liabilitas pajak'],
            'utang_lain'          => ['utang lain', 'other payable', 'utang lain-lain', 'hutang lain', 'liabilitas lain'],
            'pendapatan_bunga'    => ['pendapatan bunga', 'interest income', 'penghasilan bunga'],
            'pendapatan_dividen'  => ['pendapatan dividen', 'dividend income', 'penghasilan dividen'],
            'pendapatan_investasi' => ['pendapatan investasi', 'investment income', 'penghasilan investasi'],
            'pendapatan_lainnya'  => ['pendapatan lainnya', 'other income', 'pendapatan lain-lain', 'penghasilan lain'],
            'gain_realized'       => ['gain realized', 'keuntungan realisasi', 'laba realisasi', 'keuntungan investasi yang telah direalisasi', 'keuntungan yang telah direalisasi'],
            'gain_unrealized'     => ['gain unrealized', 'keuntungan belum realisasi', 'laba belum realisasi', 'unrealized gain', 'keuntungan investasi yang belum direalisasi', 'keuntungan yang belum direalisasi'],
            'beban_mi'            => ['beban manajer investasi', 'beban mi', 'investment manager fee', 'imbal jasa manajer', 'beban pengelolaan investasi', 'biaya jasa pengelolaan'],
            'beban_kustodian'     => ['beban kustodian', 'custodian fee expense', 'imbal jasa kustodian', 'beban jasa kustodian', 'biaya jasa kustodian'],
            'beban_investasi'     => ['beban investasi', 'investment expense', 'beban investasi lain'],
            'beban_pengelolaan_investasi' => ['beban pengelolaan investasi', 'investment management expense'],
            'beban_lain'          => ['beban lain', 'other expense', 'beban lain-lain', 'beban lainnya'],
            'laba_bersih'         => ['laba bersih', 'net income', 'net profit', 'kenaikan aset bersih', 'kenaikan bersih aset', 'penurunan aset bersih', 'kenaikan (penurunan) aset bersih'],
            'arus_kas_operasi'    => ['arus kas operasi', 'cash flow from operating', 'kas dari aktivitas operasi', 'kas bersih dari aktivitas operasi', 'arus kas dari aktivitas operasi'],
            'kas_bersih_aktivitas_operasi' => ['kas bersih aktivitas operasi', 'net cash from operating'],
            'pembelian_efek_ekuitas' => ['pembelian efek ekuitas', 'purchase of equity securities'],
            'penjualan_efek_ekuitas' => ['penjualan efek ekuitas', 'sale of equity securities'],
            'penerimaan_bunga_deposito' => ['penerimaan bunga deposito', 'interest received from deposits'],
            'penerimaan_bunga_jasa_giro' => ['penerimaan bunga jasa giro', 'interest received from current accounts'],
            'penerimaan_dividen_kas' => ['penerimaan dividen kas', 'cash dividend received'],
            'pembayaran_jasa_pengelolaan' => ['pembayaran jasa pengelolaan', 'payment for management services'],
            'pembayaran_jasa_kustodian' => ['pembayaran jasa kustodian', 'payment for custodian services'],
            'pembayaran_beban_lain_arus' => ['pembayaran beban lain', 'payment for other expenses'],
            'arus_kas_pendanaan'  => ['arus kas pendanaan', 'cash flow from financing', 'kas dari aktivitas pendanaan', 'kas bersih dari aktivitas pendanaan', 'arus kas dari aktivitas pendanaan'],
            'penerimaan_penjualan_unit' => ['penerimaan penjualan unit', 'proceeds from unit sales'],
            'pembayaran_pembelian_kembali_unit' => ['pembayaran pembelian kembali unit', 'payment for unit repurchase'],
            'kas_bersih_aktivitas_pendanaan' => ['kas bersih aktivitas pendanaan', 'net cash from financing'],
            'kenaikan_kas_setara_kas' => ['kenaikan kas dan setara kas', 'increase in cash and cash equivalents', 'kenaikan kas setara kas'],
            'kas_awal_tahun'      => ['kas awal', 'cash at beginning', 'kas awal tahun', 'kas dan setara kas awal', 'saldo awal kas'],
            'kas_akhir_tahun'     => ['kas akhir', 'cash at end', 'kas akhir tahun', 'kas dan setara kas akhir', 'saldo akhir kas'],
            'fair_value_level_1'  => ['fair value level 1', 'nilai wajar level 1', 'tingkat 1', 'level 1'],
            'fair_value_level_2'  => ['fair value level 2', 'nilai wajar level 2', 'tingkat 2', 'level 2'],
            'fair_value_level_3'  => ['fair value level 3', 'nilai wajar level 3', 'tingkat 3', 'level 3'],
            'unit_milik_investor' => ['unit milik investor', 'units held by investors', 'unit penyertaan pemegang', 'pemegang unit penyertaan', 'unit penyertaan yang dimiliki investor', 'dimiliki pemegang unit'],
            'unit_milik_mi'       => ['unit milik manajer', 'units held by manager', 'unit milik mi', 'dimiliki manajer investasi', 'unit penyertaan manajer investasi'],
            'total_unit_beredar'  => ['total unit beredar', 'total units outstanding', 'unit beredar', 'jumlah unit penyertaan beredar', 'total unit penyertaan beredar', 'unit penyertaan beredar'],
        ];

        $percentMap = [
            'total_hasil_investasi'          => ['total hasil investasi', 'total investment return', 'hasil investasi', 'jumlah hasil investasi'],
            'hasil_investasi_setelah_biaya'  => ['hasil investasi setelah biaya', 'investment return after marketing', 'hasil investasi setelah memperhitungkan', 'setelah biaya pemasaran', 'hasil investasi bersih'],
            'persentase_pph'                 => ['persentase pph', 'penghasilan kena pajak', 'taxable income percentage', 'persentase penghasilan kena pajak', 'pph yang bersifat final'],
            'biaya_operasi'                  => ['biaya operasi', 'expense ratio', 'operating expense', 'rasio biaya', 'rasio biaya operasi', 'beban operasi'],
            'portfolio_turnover_ratio'       => ['portfolio turnover', 'turnover ratio', 'rasio perputaran', 'perputaran portofolio', 'portfolio turnover ratio'],
            'total_return'                   => ['total return', 'total imbal hasil', 'imbal hasil total'],
        ];

        foreach ($fieldMap as $field => $labels) {
            $found = null;
            foreach ($lines as $line) {
                $val = $this->extractLabelValue($line, $labels);
                if ($val !== null && $val != 0) {
                    $found = $val;
                    break;
                }
            }
            if ($found === null) {
                $found = $this->extractLabelAcrossLines($fullText, $labels, false);
            }
            if ($found !== null) {
                $result[$field] = $found;
            }
        }

        foreach ($percentMap as $field => $labels) {
            $found = null;
            foreach ($lines as $line) {
                $val = $this->extractLabelPercent($line, $labels);
                if ($val !== null) {
                    $found = $val;
                    break;
                }
            }
            if ($found === null) {
                $found = $this->extractLabelAcrossLines($fullText, $labels, true);
            }
            if ($found !== null) {
                $result[$field] = $found;
            }
        }

        return $result;
    }

    /**
     * Multi-line fallback untuk field LK yang label dan angkanya berada di baris
     * berbeda akibat PDF text extraction yang ter-wrap. Mencari label di fullText
     * lalu ambil angka rupiah/persen pertama dalam jendela ~400 karakter setelahnya.
     */
    private function extractLabelAcrossLines(string $fullText, array $labels, bool $isPercent): ?float
    {
        $lower = strtolower($fullText);
        foreach ($labels as $label) {
            $labelLower = strtolower($label);
            $pos = strpos($lower, $labelLower);
            if ($pos === false) {
                continue;
            }
            // Ambil konteks 400 char setelah label
            $context = substr($fullText, $pos + strlen($label), 400);
            if ($isPercent) {
                // Cari angka persen pertama (dengan atau tanpa tanda %)
                if (preg_match('/([\-+]?[\d][\d.,]*\d?)\s*%/', $context, $m)) {
                    $val = (float) str_replace(',', '.', $m[1]);
                    if (abs($val) < 1000) {
                        return $val;
                    }
                }
                if (preg_match('/[\s:]+([\-+]?[\d][\d.,]{0,12})/', $context, $m)) {
                    $val = (float) str_replace(',', '.', $m[1]);
                    if (abs($val) < 1000) {
                        return $val;
                    }
                }
            } else {
                // Cari angka rupiah pertama, support suffix miliar/triliun/juta
                if (preg_match('/([\d][\d.,]*(?:[.,]\d+)?)\s*(miliar|milyar|triliun|juta)?/i', $context, $m)) {
                    $raw = $m[1];
                    $value = str_replace(['.', ','], ['', '.'], $raw);
                    $val = (float) $value;
                    if ($val <= 0) {
                        continue;
                    }
                    $suffix = strtolower($m[0]);
                    if (str_contains($suffix, 'triliun')) return $val * 1000000000000;
                    if (str_contains($suffix, 'miliar') || str_contains($suffix, 'milyar')) return $val * 1000000000;
                    if (str_contains($suffix, 'juta')) return $val * 1000000;
                    return $val;
                }
            }
        }
        return null;
    }

    /**
     * Detect section type from text content using keyword analysis.
     */
    public function detectSectionType(string $text): string
    {
        $lower = strtolower($text);

        $score = [
            'pengukuran_nilai_wajar' => 0,
            'bs_is_cf_pup' => 0,
            'portofolio_efek' => 0,
            'informasi_lainnya' => 0,
        ];

        // Keywords for fair value / unit info
        if (preg_match('/fair\s*value|nilai\s*wajar|level\s*[123]|tingkat\s*[123]/i', $lower)) {
            $score['pengukuran_nilai_wajar'] += 3;
        }
        if (preg_match('/unit\s*(penyertaan|milik|beredar)|pemegang\s*unit|total\s*unit/i', $lower)) {
            $score['pengukuran_nilai_wajar'] += 2;
        }

        // Keywords for financial statements (BS, IS, CF)
        if (preg_match('/total\s*aset|total\s*liabilitas|neraca|balance\s*sheet/i', $lower)) {
            $score['bs_is_cf_pup'] += 3;
        }
        if (preg_match('/laba\s*bersih|net\s*income|laporan\s*lab[ea] rugi|income\s*statement|pendapatan\s*bunga|beban\s*mi|beban\s*kustodian|gain\s*realized|gain\s*unrealized/i', $lower)) {
            $score['bs_is_cf_pup'] += 3;
        }
        if (preg_match('/arus\s*kas|cash\s*flow|kas\s*(awal|akhir)|aktivitas\s*operasi/i', $lower)) {
            $score['bs_is_cf_pup'] += 3;
        }
        if (preg_match('/total\s*hasil\s*investasi|hasil\s*investasi\s*setelah|biaya\s*operasi|portfolio\s*turnover|penghasilan\s*kena\s*pajak/i', $lower)) {
            $score['bs_is_cf_pup'] += 2;
        }

        // Keywords for portfolio / holdings
        if (preg_match('/portofolio|efek|saham|equity\s*instr|obligasi|bond|sukuk|sektor|alokasi\s*aset/i', $lower)) {
            $score['portofolio_efek'] += 3;
        }
        if (preg_match('/bobot|nilai\s*pasar|harga\s*perolehan|persen\s*nab|yield|kupon|rating|jatuh\s*tempo/i', $lower)) {
            $score['portofolio_efek'] += 2;
        }
        if (preg_match('/bank|kas\s*di\s*bank|pasar\s*uang|deposito|piutang\s*bunga/i', $lower)) {
            $score['portofolio_efek'] += 1;
        }

        // Keywords for general fund info
        if (preg_match('/reksa\s*dana|manajer\s*investasi|bank\s*kustodian|tujuan\s*investasi|kebijakan\s*investasi|benchmark/i', $lower)) {
            $score['informasi_lainnya'] += 2;
        }
        if (preg_match('/return|imbal\s*hasil|aum|nab|unit\s*penyertaan|tanggal\s*peluncuran/i', $lower)) {
            $score['informasi_lainnya'] += 1;
        }

        // Default fallback
        if (array_sum($score) === 0) {
            return 'informasi_lainnya';
        }

        arsort($score);
        return key($score);
    }

    /**
     * Smart merge multiple parse results into one.
     * - Scalar fields: last non-empty value wins
     * - Array fields: longest array wins
     */
    public function smartMergeResults(array $results): array
    {
        $merged = [];
        $arrayFields = ['alokasi_aset', 'sektor', 'efek', 'kinerja', 'obligasi', 'sukuk', 'bank', 'pasar_uang', 'piutang_bunga_detail', 'kategori'];

        foreach ($results as $data) {
            if (!is_array($data)) continue;
            foreach ($data as $key => $value) {
                if ($value === null || $value === '' || (is_array($value) && empty($value))) {
                    continue;
                }
                if (in_array($key, $arrayFields)) {
                    if (!isset($merged[$key]) || !is_array($merged[$key]) || count($value) > count($merged[$key])) {
                        $merged[$key] = $value;
                    }
                } else {
                    $merged[$key] = $value;
                }
            }
        }

        return $merged;
    }

    /**
     * Parse PDF with multiple page ranges. Each range is sent to AI with the appropriate prompt.
     */
    public function parseWithPageRanges(string $pdfPath, GroqService $groq, array $pageRanges): array
    {
        set_time_limit(300);
        $parser = new Parser;
        $pdf = $parser->parseFile($pdfPath);

        // Get full document text first (fallback for page-level extraction failures)
        $fullText = $pdf->getText();
        $fullText = implode("\n", array_filter(array_map('trim', explode("\n", $fullText))));

        // Vision fallback jika scanned PDF
        if (mb_strlen($fullText) < 500) {
            \Log::info('[PARSE-PAGE-RANGE] Scanned PDF detected, using vision fallback');
            try {
                $vision = $groq->parseFfsPdfVision($pdfPath, basename($pdfPath));
                return $this->normalizeAiData($vision);
            } catch (\Throwable $e) {
                \Log::warning('[PARSE-PAGE-RANGE] Vision fallback failed: ' . $e->getMessage());
                return $this->normalizeAiData([]);
            }
        }

        // Try page-level extraction
        $pageTexts = [];
        try {
            foreach ($pdf->getPages() as $page) {
                $pageTexts[] = $page->getText();
            }
        } catch (\Throwable) {
            $pageTexts = [];
        }

        $totalPages = count($pageTexts);
        $results = [];

        foreach ($pageRanges as $idx => $range) {
            $start = max(1, (int) ($range['start_page'] ?? 1));
            $end = min($totalPages, (int) ($range['end_page'] ?? $totalPages));

            // If no pages extracted, use full text for all ranges
            if ($totalPages === 0) {
                $text = mb_substr($fullText, 0, 60000);
            } else {
                if ($start > $end || $start > $totalPages) {
                    \Log::warning("[PARSE-PAGE-RANGE] Range #{$idx} invalid: start={$start}, end={$end}, totalPages={$totalPages}");
                    continue;
                }

                $text = '';
                for ($i = $start - 1; $i < $end; $i++) {
                    if (isset($pageTexts[$i])) {
                        $text .= $pageTexts[$i] . "\n\n";
                    }
                }

                if (mb_strlen(trim($text)) < 50) {
                    \Log::warning("[PARSE-PAGE-RANGE] Range #{$idx} (pages {$start}-{$end}) too little text, using full text fallback");
                    $text = mb_substr($fullText, 0, 60000);
                }
            }

            $sectionType = $range['section_type'] ?? 'auto';

            \Log::info("[PARSE-PAGE-RANGE] Range #{$idx}: pages {$start}-{$end}, type={$sectionType}, text=" . mb_strlen($text) . " chars");

            try {
                if ($sectionType === 'auto') {
                    $aiResult = $groq->parseFfsPdf($text, null);
                } else {
                    $aiResult = match ($sectionType) {
                        'informasi_lainnya' => $groq->parseInformasiLainnya($text),
                        'portofolio_efek' => $groq->parsePortofolioEfek($text),
                        'pengukuran_nilai_wajar' => $groq->parsePengukuranNilaiWajar($text),
                        'bs_is_cf_pup' => $groq->parseBsIsCfPup($text),
                        default => $groq->parseFfsPdf($text, $sectionType),
                    };
                }

                $normalized = $this->normalizeAiData($aiResult);
                $results[] = $normalized;

                \Log::info("[PARSE-PAGE-RANGE] Range #{$idx} success: " . count(array_filter($normalized, fn($v) => !empty($v))) . " fields");
            } catch (\Throwable $e) {
                \Log::warning("[PARSE-PAGE-RANGE] Range #{$idx} AI error: " . $e->getMessage());
            }
        }

        if (empty($results)) {
            \Log::warning('[PARSE-PAGE-RANGE] No results from any range, falling back to full document parse');
            // Final fallback: parse full document
            try {
                $aiResult = $groq->parseFfsPdf($fullText, null);
                return $this->normalizeAiData($aiResult);
            } catch (\Throwable $e) {
                \Log::warning('[PARSE-PAGE-RANGE] Final fallback failed: ' . $e->getMessage());
                return $this->normalizeAiData([]);
            }
        }

        $merged = $this->smartMergeResults($results);
        \Log::info('[PARSE-PAGE-RANGE] Merged: ' . count(array_filter($merged, fn($v) => !empty($v))) . ' fields from ' . count($results) . ' ranges');

        return $this->normalizeAiData($merged);
    }

    public function parseWithPageRangesHybrid(string $pdfPath, GroqService $groq, array $pageRanges, AiTableService $aiTable): array
    {
        set_time_limit(600);

        $aiData = $this->parseWithPageRanges($pdfPath, $groq, $pageRanges);

        $partitions = [];
        foreach ($pageRanges as $i => $range) {
            $partitions[] = [
                'id' => $i + 1,
                'start' => (int) ($range['start_page'] ?? 1),
                'end' => (int) ($range['end_page'] ?? 1),
                'section_type' => $range['section_type'] ?? 'auto',
            ];
        }

        try {
            $tablePartitions = $aiTable->extractTables($pdfPath, $partitions);
            $aiData['_raw_tables'] = $tablePartitions;
            \Log::info('[PARSE-HYBRID] Tables extracted from ' . count($tablePartitions) . ' partitions');
        } catch (\Throwable $e) {
            \Log::warning('[PARSE-HYBRID] Table extraction failed: ' . $e->getMessage());
            $aiData['_raw_tables'] = [];
        }

        return $aiData;
    }
}
