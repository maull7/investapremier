<?php

namespace App\Services;

class ProspektusPipelineService
{
    private PageClassifierService $classifier;
    private GroqService $groq;
    private ProspektusValidator $validator;
    private FfsParserService $ffsParser;
    private ParserClient $parserClient;

    public function __construct(
        PageClassifierService $classifier,
        GroqService $groq,
        ProspektusValidator $validator,
        FfsParserService $ffsParser,
        ParserClient $parserClient
    ) {
        $this->classifier = $classifier;
        $this->groq = $groq;
        $this->validator = $validator;
        $this->ffsParser = $ffsParser;
        $this->parserClient = $parserClient;
    }

    public function process(string $pdfPath, string $documentType = 'prospektus'): array
    {
        if ($documentType !== 'prospektus') {
            return $this->ffsParser->parseWithAi($pdfPath, $this->groq);
        }

        $pdfParser = new \Smalot\PdfParser\Parser;
        $pdf = $pdfParser->parseFile($pdfPath);

        $pageTexts = [];
        foreach ($pdf->getPages() as $page) {
            $pageTexts[] = $page->getText();
        }

        if (empty($pageTexts)) {
            throw new \RuntimeException('Tidak dapat membaca halaman PDF');
        }

        $classifications = $this->classifier->classifyPages($pageTexts);

        $pageGroups = $this->classifier->groupPagesBySection($classifications);

        $extracted = $this->extractAllSections($pageTexts, $pageGroups, $pdfPath);

        $merged = $this->mergeExtractedData($extracted);

        $merged = $this->normalizeData($merged);

        $valid = $this->validator->validate($merged);

        \Log::info('[PROSPEKTUS] Validation result', [
            'valid' => $valid,
            'errors' => $this->validator->getErrors(),
            'warnings' => $this->validator->getWarnings(),
        ]);

        return [
            'data' => $merged,
            'classifications' => $classifications,
            'page_groups' => $pageGroups,
            'section_results' => $extracted,
            'validation' => [
                'valid' => $valid,
                'errors' => $this->validator->getErrors(),
                'warnings' => $this->validator->getWarnings(),
            ],
        ];
    }

    private function extractAllSections(array $pageTexts, array $pageGroups, string $pdfPath): array
    {
        $sectionOrder = [
            PageClassifierService::SECTION_COVER,
            PageClassifierService::SECTION_MI_PROFILE,
            PageClassifierService::SECTION_FUND_INFO,
            PageClassifierService::SECTION_PORTFOLIO,
            PageClassifierService::SECTION_PERFORMANCE,
            PageClassifierService::SECTION_RISK,
            PageClassifierService::SECTION_FINANCIAL_STATEMENTS,
        ];

        $extracted = [];

        $parserEnabled = $this->parserClient->enabled();
        $parserSections = ['cover', 'fund_info', 'portfolio', 'performance', 'financial_statements'];
        $parserResults = [];

        if ($parserEnabled) {
            $parserResults = $this->parserClient->extractAllSections($pdfPath, $pageGroups);
        }

        foreach ($sectionOrder as $section) {
            $pages = $pageGroups[$section] ?? [];

            if ($parserEnabled && in_array($section, $parserSections)) {
                $result = $parserResults[$section] ?? [];
                if (!empty($result)) {
                    $extracted[$section] = $result;
                    \Log::info('[PROSPEKTUS] Parser result for: ' . $section, [
                        'fields' => array_keys($result),
                    ]);

                    if ($section === PageClassifierService::SECTION_FINANCIAL_STATEMENTS) {
                        $extracted[$section] = $this->applyFinancialFallback($extracted[$section] ?? [], $pageTexts, $pages, $pdfPath);
                    }
                    continue;
                }
                \Log::info('[PROSPEKTUS] Parser empty for: ' . $section . ', fallback ke Groq');
            }

            if ($section === PageClassifierService::SECTION_FINANCIAL_STATEMENTS) {
                $combinedText = '';
                foreach ($pages as $pageNum) {
                    if (isset($pageTexts[$pageNum])) {
                        $combinedText .= $pageTexts[$pageNum] . "\n\n";
                    }
                }
                $combinedText = trim($combinedText);

                if (!empty($combinedText)) {
                    try {
                        $result = $this->groq->parseProspektusSection($section, $combinedText);
                        $extracted[$section] = $result;
                        \Log::info('[PROSPEKTUS] Groq financial: ' . count($pages) . ' halaman, fields: ' . count(array_keys($result)));
                    } catch (\Throwable $e) {
                        \Log::warning('[PROSPEKTUS] Groq financial error: ' . $e->getMessage());
                        $extracted[$section] = [];
                    }
                } else {
                    $extracted[$section] = [];
                    \Log::info('[PROSPEKTUS] Tidak ada teks financial, vision fallback');
                }

                $extracted[$section] = $this->applyFinancialFallback($extracted[$section] ?? [], $pageTexts, $pages, $pdfPath);
                continue;
            }

            $combinedText = '';
            foreach ($pages as $pageNum) {
                if (isset($pageTexts[$pageNum])) {
                    $combinedText .= $pageTexts[$pageNum] . "\n\n";
                }
            }
            $combinedText = trim($combinedText);
            if (empty($combinedText)) continue;

            try {
                $result = $this->groq->parseProspektusSection($section, $combinedText);
                $extracted[$section] = $result;
                \Log::info('[PROSPEKTUS] Groq section: ' . $section, [
                    'fields' => array_keys($result),
                ]);
            } catch (\Throwable $e) {
                \Log::warning('[PROSPEKTUS] Groq section error: ' . $section . ' - ' . $e->getMessage());
                $extracted[$section] = [];
            }
        }

        return $extracted;
    }

    private function applyFinancialFallback(array $aiResult, array $allPageTexts, array $financialPages, string $pdfPath): array
    {
        $aiFields = array_keys(array_filter($aiResult, fn($v) => $v !== null && $v !== '' && $v !== []));
        $expectedFinancialFields = [
            'total_aset', 'total_liabilitas', 'kas_dan_bank', 'laba_bersih',
            'pendapatan_bunga', 'pendapatan_dividen', 'arus_kas_operasi',
            'fair_value_level_1', 'unit_milik_investor',
        ];

        $merged = $aiResult;

        if (!empty($financialPages)) {
            $foundEssential = array_intersect($aiFields, $expectedFinancialFields);

            if (count($foundEssential) < 3) {
                \Log::info('[PROSPEKTUS] AI financial hanya ' . count($foundEssential) . ' essential fields, coba regex');

                $lines = [];
                $fullText = '';
                foreach ($financialPages as $pageNum) {
                    if (isset($allPageTexts[$pageNum])) {
                        $pageLines = array_filter(array_map('trim', explode("\n", $allPageTexts[$pageNum])));
                        $lines = array_merge($lines, $pageLines);
                        $fullText .= $allPageTexts[$pageNum] . "\n";
                    }
                }

                $regexResult = $this->ffsParser->extractLaporanKeuangan($lines, $fullText);

                foreach ($regexResult as $field => $value) {
                    if ($value !== null && $value !== '' && $value !== 0) {
                        if (empty($merged[$field]) && $merged[$field] !== 0) {
                            $merged[$field] = $value;
                        }
                    }
                }

                $afterRegex = array_keys(array_filter($merged, fn($v) => $v !== null && $v !== '' && $v !== []));
                $foundEssentialAfterRegex = array_intersect($afterRegex, $expectedFinancialFields);
                if (count($foundEssentialAfterRegex) >= 3) {
                    \Log::info('[PROSPEKTUS] Financial after regex: ' . count($afterRegex) . ' fields, cukup');
                    return $merged;
                }
            } else {
                \Log::info('[PROSPEKTUS] AI sudah cukup (' . count($foundEssential) . ' fields), skip regex');
            }
        }

        $filled = array_keys(array_filter($merged, fn($v) => $v !== null && $v !== '' && $v !== []));
        $foundEssential = array_intersect($filled, $expectedFinancialFields);

        if (count($foundEssential) >= 3) {
            \Log::info('[PROSPEKTUS] Financial cukup (' . count($filled) . ' fields), skip vision');
            return $merged;
        }

        \Log::info('[PROSPEKTUS] Financial belum cukup (' . count($foundEssential) . ' essential), jalankan vision fallback');

        try {
            $visionResult = $this->groq->parseProspektusFinancialVision($pdfPath);
            foreach ($visionResult as $field => $value) {
                if ($value !== null && $value !== '' && $value !== [] && $value !== 0) {
                    if (empty($merged[$field]) && $merged[$field] !== 0) {
                        $merged[$field] = $value;
                    }
                }
            }
        } catch (\Throwable $e) {
            \Log::warning('[PROSPEKTUS] Vision fallback gagal: ' . $e->getMessage());
        }

        $filled = array_keys(array_filter($merged, fn($v) => $v !== null && $v !== '' && $v !== []));
        \Log::info('[PROSPEKTUS] Financial akhir: ' . count($filled) . ' fields terisi');
        return $merged;
    }

    private function mergeExtractedData(array $extracted): array
    {
        $merged = [];

        $fieldHierarchy = [
            PageClassifierService::SECTION_FUND_INFO => [
                'nama_reksa_dana', 'jenis_reksa_dana', 'kategori', 'manajer_investasi',
                'bank_kustodian', 'mata_uang', 'benchmark', 'tujuan_investasi',
                'kebijakan_investasi', 'total_aum', 'unit_penyertaan', 'nab_per_unit',
                'tanggal_data', 'ffs_bulan', 'ffs_tahun', 'return_ytd', 'return_1y',
                'total_return', 'biaya_operasi', 'portfolio_turnover_ratio',
                'management_fee', 'custodian_fee', 'total_marcap_10_efek',
                'tanggal_peluncuran',
            ],
            PageClassifierService::SECTION_FINANCIAL_STATEMENTS => [
                'total_aset', 'total_liabilitas', 'kas_dan_bank', 'piutang_bunga',
                'piutang_dividen', 'piutang_lain', 'utang_pajak', 'utang_lain',
                'pendapatan_bunga', 'pendapatan_dividen', 'gain_realized',
                'gain_unrealized', 'beban_mi', 'beban_kustodian', 'beban_lain',
                'laba_bersih', 'arus_kas_operasi', 'arus_kas_pendanaan',
                'kas_awal_tahun', 'kas_akhir_tahun', 'total_hasil_investasi',
                'hasil_investasi_setelah_biaya', 'persentase_pph',
                'fair_value_level_1', 'fair_value_level_2', 'fair_value_level_3',
                'unit_milik_investor', 'unit_milik_mi', 'total_unit_beredar',
            ],
            PageClassifierService::SECTION_PORTFOLIO => [
                'alokasi_aset', 'sektor', 'efek', 'obligasi', 'sukuk', 'bank',
            ],
            PageClassifierService::SECTION_PERFORMANCE => [
                'kinerja',
            ],
            PageClassifierService::SECTION_MI_PROFILE => [
                'alamat_mi', 'telepon_mi', 'email_mi', 'website_mi',
                'komisaris_utama', 'direktur_utama', 'daftar_komisaris',
                'daftar_direksi', 'daftar_pemegang_saham', 'deskripsi_mi',
            ],
        ];

        foreach ($fieldHierarchy as $section => $fields) {
            $sectionData = $extracted[$section] ?? [];
            foreach ($fields as $field) {
                if (array_key_exists($field, $sectionData)) {
                    $value = $sectionData[$field];
                    if ($value !== null && $value !== '' && $value !== []) {
                        if (is_numeric($merged[$field] ?? null) && !is_numeric($value)) {
                            continue;
                        }
                        $merged[$field] = $value;
                    }
                }
            }
        }

        $merged = $this->fillFromAnySection($merged, $extracted, [
            'nama_reksa_dana', 'jenis_reksa_dana', 'manajer_investasi', 'bank_kustodian',
        ]);

        $arrayFields = ['alokasi_aset', 'sektor', 'efek', 'kinerja', 'obligasi', 'sukuk', 'bank', 'kategori'];
        foreach ($arrayFields as $field) {
            if (!isset($merged[$field]) || !is_array($merged[$field])) {
                $merged[$field] = [];
            }
        }

        return $merged;
    }

    private function fillFromAnySection(array $merged, array $extracted, array $fields): array
    {
        foreach ($fields as $field) {
            if (!empty($merged[$field])) continue;
            foreach ($extracted as $sectionData) {
                if (!empty($sectionData[$field])) {
                    $merged[$field] = $sectionData[$field];
                    break;
                }
            }
        }
        return $merged;
    }

    private function normalizeData(array $data): array
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
            'arus_kas_operasi' => null,
            'arus_kas_pendanaan' => null,
            'kas_awal_tahun' => null,
            'kas_akhir_tahun' => null,
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
            'alamat_mi' => null,
            'telepon_mi' => null,
            'email_mi' => null,
            'website_mi' => null,
            'komisaris_utama' => null,
            'direktur_utama' => null,
            'daftar_komisaris' => null,
            'daftar_direksi' => null,
            'daftar_pemegang_saham' => null,
            'deskripsi_mi' => null,
        ];

        $data = array_merge($defaults, array_intersect_key($data, $defaults));

        if (is_string($data['kategori'])) {
            $data['kategori'] = array_values(array_filter(array_map('trim', preg_split('/[,;|]/', $data['kategori']))));
        }
        if (!is_array($data['kategori'])) {
            $data['kategori'] = [];
        }

        foreach (['alokasi_aset', 'sektor', 'efek', 'kinerja', 'obligasi', 'sukuk', 'bank'] as $field) {
            $data[$field] = is_array($data[$field]) ? array_values($data[$field]) : [];
        }

        if (!empty($data['tanggal_data']) && (empty($data['ffs_bulan']) || empty($data['ffs_tahun']))) {
            try {
                $date = \Carbon\Carbon::parse($data['tanggal_data']);
                $data['ffs_bulan'] = $data['ffs_bulan'] ?: $date->month;
                $data['ffs_tahun'] = $data['ffs_tahun'] ?: $date->year;
            } catch (\Throwable) {
            }
        }

        return $data;
    }
}
