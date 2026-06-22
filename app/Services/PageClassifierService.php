<?php

namespace App\Services;

class PageClassifierService
{
    const SECTION_COVER = 'cover';
    const SECTION_MI_PROFILE = 'mi_profile';
    const SECTION_FUND_INFO = 'fund_info';
    const SECTION_FINANCIAL_STATEMENTS = 'financial_statements';
    const SECTION_PORTFOLIO = 'portfolio';
    const SECTION_PERFORMANCE = 'performance';
    const SECTION_RISK = 'risk';
    const SECTION_OTHER = 'other';

    const SECTIONS = [
        self::SECTION_COVER => [
            'cover', 'prospektus', 'reksa dana', 'fund prospectus', 'informasi umum',
            'prospectus', 'ditawarkan', 'penawaran umum',
        ],
        self::SECTION_MI_PROFILE => [
            'manajer investasi', 'investment manager', 'dewan komisaris',
            'board of commissioners', 'direksi', 'board of directors',
            'komisaris utama', 'direktur utama', 'presiden komisaris',
            'presiden direktur', 'pemegang saham', 'susunan',
            'komite investasi', 'investment committee', 'tim pengelola',
            'alamat', 'berkedudukan', 'riwayat', 'pendiri',
            'investment management team', 'pengurus',
        ],
        self::SECTION_FUND_INFO => [
            'tujuan investasi', 'investment objective', 'kebijakan investasi',
            'investment policy', 'strategi investasi', 'investment strategy',
            'benchmark', 'nilai aktiva bersih', 'pembagian keuntungan',
            'manajer investasi', 'bank kustodian', 'informasi reksa dana',
            'jenis reksa dana', 'mata uang', 'metode penghitungan',
            'klasifikasi', 'investasi', 'pembelian', 'penjualan',
            'pengalihan', 'pelunasan', 'biaya', 'imbalan',
        ],
        self::SECTION_FINANCIAL_STATEMENTS => [
            'laporan keuangan', 'neraca', 'laba rugi', 'arus kas',
            'financial statements', 'balance sheet', 'income statement',
            'cash flow', 'total aset', 'total liabilitas', 'ekuitas',
            'pendapatan', 'beban', 'catatan atas laporan keuangan',
            'laporan perubahan', 'laporan posisi keuangan',
            'nilai wajar', 'fair value', 'aset bersih',
            'laba bersih', 'penghasilan komprehensif',
            'laporan laba rugi', 'laporan penghasilan komprehensif',
            'laporan posisi keuangan', 'laporan arus kas',
            'neraca per', 'neraca tanggal', 'laporan perubahan aset bersih',
            'perhitungan hasil investasi', 'perhitungan nab',
            'perhitungan unit penyertaan', 'laporan portofolio efek',
            'portofolio reksa dana', 'tabel fair value',
            'pengukuran nilai wajar', 'hierarki nilai wajar',
            'unit beredar', 'unit penyertaan beredar',
        ],
        self::SECTION_PORTFOLIO => [
            'portofolio', 'alokasi aset', 'asset allocation', 'komposisi',
            'efek', 'sektor', 'obligasi', 'sukuk', 'deposito',
            'holding', 'investasi', 'saham', 'reksa dana lain',
            'surat utang', 'pasar uang', 'top holding',
        ],
        self::SECTION_PERFORMANCE => [
            'kinerja', 'imbal hasil', 'return', 'performance',
            'hasil investasi', 'perkembangan', 'tingkat pengembalian',
            'perbandingan', 'grafik kinerja', 'data historis',
        ],
        self::SECTION_RISK => [
            'risiko', 'risk', 'profil risiko', 'manajemen risiko',
            'risk management', 'faktor risiko', 'mitigasi',
        ],
    ];

    public function classifyPages(array $pageTexts, ?\Closure $fallbackAi = null): array
    {
        $classifications = [];
        $total = count($pageTexts);

        foreach ($pageTexts as $pageNum => $text) {
            $score = $this->scorePage($text);
            $top = $this->getTopSection($score);
            $classifications[$pageNum] = [
                'section' => $top,
                'score' => $score,
                'confidence' => $score[$top] ?? 0,
            ];
        }

        $smooth = $this->smoothClassifications($classifications, $total);

        if ($fallbackAi) {
            $lowConfidence = array_filter($smooth, fn($c) => $c['confidence'] < 2 || $c['section'] === self::SECTION_OTHER);
            if (!empty($lowConfidence)) {
                try {
                    $aiFixed = $fallbackAi($pageTexts, $smooth);
                    foreach ($aiFixed as $pageNum => $section) {
                        if (isset($smooth[$pageNum]) && $section !== self::SECTION_OTHER) {
                            $smooth[$pageNum]['section'] = $section;
                            $smooth[$pageNum]['confidence'] = 5;
                        }
                    }
                } catch (\Throwable $e) {
                    \Log::warning('[CLASSIFIER] AI fallback failed: ' . $e->getMessage());
                }
            }
        }

        return $smooth;
    }

    private function scorePage(string $text): array
    {
        $scores = [];
        $lower = strtolower($text);

        foreach (self::SECTIONS as $section => $keywords) {
            $score = 0;
            foreach ($keywords as $kw) {
                $count = mb_substr_count($lower, $kw);
                if ($count > 0) {
                    $score += $count * 2;
                    if ($this->isExactMatch($lower, $kw)) {
                        $score += 3;
                    }
                }
            }
            $scores[$section] = $score;
        }

        $scores[self::SECTION_OTHER] = 0;

        return $scores;
    }

    private function isExactMatch(string $text, string $keyword): bool
    {
        return preg_match('/\b' . preg_quote($keyword, '/') . '\b/i', $text) === 1;
    }

    private function getTopSection(array $scores): string
    {
        arsort($scores);
        return key($scores);
    }

    private function smoothClassifications(array $classifications, int $total): array
    {
        if ($total <= 3) return $classifications;

        $result = [];

        foreach ($classifications as $pageNum => $classification) {
            $section = $classification['section'];

            $neighbors = [];
            for ($i = max(0, $pageNum - 1); $i <= min($total - 1, $pageNum + 1); $i++) {
                if ($i !== $pageNum && isset($classifications[$i])) {
                    $neighbors[] = $classifications[$i]['section'];
                }
            }

            $uniqueNeighbors = array_unique($neighbors);
            if (count($uniqueNeighbors) === 1 && $uniqueNeighbors[0] !== $section) {
                $sameAsNeighbor = count(array_filter($neighbors, fn($n) => $n === $uniqueNeighbors[0]));
                if ($sameAsNeighbor >= 2) {
                    $section = $uniqueNeighbors[0];
                }
            }

            $result[$pageNum] = [
                'section' => $section,
                'score' => $classifications[$pageNum]['score'],
                'confidence' => $classifications[$pageNum]['confidence'],
            ];
        }

        $result = $this->mergeShortSpans($result, $total);

        return $result;
    }

    private function mergeShortSpans(array $classifications, int $total): array
    {
        $sections = array_column($classifications, 'section');

        $spanStart = 0;
        $currentSection = $sections[0] ?? self::SECTION_OTHER;

        $spans = [];
        foreach ($sections as $i => $section) {
            if ($section !== $currentSection) {
                $spans[] = ['section' => $currentSection, 'start' => $spanStart, 'end' => $i - 1];
                $spanStart = $i;
                $currentSection = $section;
            }
        }
        $spans[] = ['section' => $currentSection, 'start' => $spanStart, 'end' => $total - 1];

        foreach ($spans as $span) {
            $length = $span['end'] - $span['start'] + 1;
            if ($length <= 2 && $span['section'] !== self::SECTION_OTHER) {
                $prevSection = $this->findPrevSection($spans, $span);
                $nextSection = $this->findNextSection($spans, $span);
                if ($prevSection && $prevSection === $nextSection) {
                    for ($i = $span['start']; $i <= $span['end']; $i++) {
                        $classifications[$i]['section'] = $prevSection;
                    }
                }
            }
        }

        return $classifications;
    }

    private function findPrevSection(array $spans, array $current): ?string
    {
        $idx = array_search($current, $spans, true);
        if ($idx === false || $idx === 0) return null;
        for ($i = $idx - 1; $i >= 0; $i--) {
            if ($spans[$i]['section'] !== self::SECTION_OTHER) {
                return $spans[$i]['section'];
            }
        }
        return null;
    }

    private function findNextSection(array $spans, array $current): ?string
    {
        $idx = array_search($current, $spans, true);
        if ($idx === false || $idx === count($spans) - 1) return null;
        for ($i = $idx + 1; $i < count($spans); $i++) {
            if ($spans[$i]['section'] !== self::SECTION_OTHER) {
                return $spans[$i]['section'];
            }
        }
        return null;
    }

    public function groupPagesBySection(array $classifications): array
    {
        $groups = [];

        foreach ($classifications as $pageNum => $classification) {
            $section = $classification['section'];
            if (!isset($groups[$section])) {
                $groups[$section] = [];
            }
            $groups[$section][] = $pageNum;
        }

        return $groups;
    }
}
