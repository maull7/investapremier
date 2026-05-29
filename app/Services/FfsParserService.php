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

        return [
            'nama_reksa_dana' => $this->safeExtract('extractNamaReksaDana', [$lines, $fullText]),
            'jenis_reksa_dana' => $this->safeExtract('extractJenisReksaDana', [$lines, $fullText]),
            'total_aum' => $this->safeExtract('extractAum', [$lines, $fullText]),
            'unit_penyertaan' => null,
            'nab_per_unit' => null,
            'total_marcap_10_efek' => $this->safeExtract('extractTotalMarcap', [$lines, $fullText]),
            'tanggal_data' => null,
            'ffs_bulan' => null,
            'ffs_tahun' => null,
            'alokasi_aset' => [],
            'sektor' => $this->safeExtract('extractSektor', [$lines, $fullText]),
            'efek' => $this->safeExtract('extractEfek', [$lines, $fullText]),
            'kinerja' => $this->safeExtract('extractKinerja', [$lines, $fullText]),
            'obligasi' => $this->safeExtract('extractObligasi', [$lines, $fullText]),
            'sukuk' => $this->safeExtract('extractSukuk', [$lines, $fullText]),
            'bank' => $this->safeExtract('extractBank', [$lines, $fullText]),
        ];
    }

    public function parseWithAi(string $pdfPath, GroqService $groq): array
    {
        $parser = new Parser;
        $pdf = $parser->parseFile($pdfPath);
        $text = $pdf->getText();

        $lines = array_filter(array_map('trim', explode("\n", $text)));
        $fullText = implode("\n", $lines);

        $regex = [
            'nama_reksa_dana' => $this->safeExtract('extractNamaReksaDana', [$lines, $fullText]),
            'jenis_reksa_dana' => $this->safeExtract('extractJenisReksaDana', [$lines, $fullText]),
            'kategori' => [],
            'total_aum' => $this->safeExtract('extractAum', [$lines, $fullText]),
            'unit_penyertaan' => null,
            'nab_per_unit' => null,
            'total_marcap_10_efek' => $this->safeExtract('extractTotalMarcap', [$lines, $fullText]),
            'tanggal_data' => null,
            'ffs_bulan' => null,
            'ffs_tahun' => null,
            'alokasi_aset' => [],
            'sektor' => $this->safeExtract('extractSektor', [$lines, $fullText]),
            'efek' => $this->safeExtract('extractEfek', [$lines, $fullText]),
            'kinerja' => $this->safeExtract('extractKinerja', [$lines, $fullText]),
            'obligasi' => $this->safeExtract('extractObligasi', [$lines, $fullText]),
            'sukuk' => $this->safeExtract('extractSukuk', [$lines, $fullText]),
            'bank' => $this->safeExtract('extractBank', [$lines, $fullText]),
        ];

        $ai = $groq->parseFfsPdf($fullText);

        return $this->merge($regex, $this->normalizeAiData($ai));
    }

    public function normalizeAiParseResult(array $ai): array
    {
        return $this->normalizeAiData($ai);
    }

    private function merge(array $regex, array $ai): array
    {
        $arrayFields = ['alokasi_aset', 'sektor', 'efek', 'kinerja', 'obligasi', 'sukuk', 'bank'];

        foreach ($regex as $key => $value) {
            $aiValue = $ai[$key] ?? null;

            if (in_array($key, $arrayFields)) {
                // Pakai AI jika regex kosong atau AI punya lebih banyak data
                if (empty($value) && !empty($aiValue)) {
                    $regex[$key] = $aiValue;
                } elseif (!empty($aiValue) && count($aiValue) > count($value)) {
                    $regex[$key] = $aiValue;
                } elseif (!empty($value) && !empty($aiValue)) {
                    // Enrich existing regex rows dengan field tambahan dari AI (sektor, kontribusi_kinerja, dll)
                    $regex[$key] = $this->enrichRows($key, $value, $aiValue);
                }
            } else {
                if ($key === 'nama_reksa_dana' && $this->looksLikeFundName($aiValue)) {
                    $regex[$key] = $aiValue;
                    continue;
                }

                // Pakai AI jika regex null/kosong
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
                    $row['market_cap']         = $row['market_cap']         ?? ($ai['market_cap'] ?? null);
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
            'total_aum' => null,
            'unit_penyertaan' => null,
            'nab_per_unit' => null,
            'total_marcap_10_efek' => null,
            'tanggal_data' => null,
            'ffs_bulan' => null,
            'ffs_tahun' => null,
            'alokasi_aset' => [],
            'sektor' => [],
            'efek' => [],
            'kinerja' => [],
            'obligasi' => [],
            'sukuk' => [],
            'bank' => [],
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

    private function extractEfek(array $lines, string $fullText): array
    {
        $efekData = [];
        $inEfek = false;

        $efekStart = ['portofolio', '10 efek', 'top 10', '10 besar', 'komposisi efek',
                       'efek', 'holding', 'saham', 'equity portfolio', 'securities'];

        foreach ($lines as $i => $line) {
            $lower = strtolower($line);

            foreach ($efekStart as $keyword) {
                if (str_contains($lower, $keyword) && strlen($lower) < 50) {
                    $inEfek = true;
                    continue 2;
                }
            }

            if ($inEfek) {
                if (count($efekData) > 0 && preg_match('/^(obligasi|kinerja|bank|total)/i', $lower)) break;

                // "BBCA Bank Central Asia 10.50" or "BBCA Bank Central Asia 10.50 9250" or "BBCA Bank Central Asia 10.50 9250 9300"
                if (preg_match('/^([A-Z]{2,6})\s+(.+?)\s+([\d.,]+)\s*%?\s*(?:([\d.,]+))?\s*(?:([\d.,]+))?$/', $line, $m)) {
                    $bobot = (float) str_replace(',', '.', $m[3]);
                    if ($bobot > 0 && $bobot <= 100) {
                        $harga = !empty($m[4]) ? (float) str_replace(',', '.', $m[4]) : null;
                        $efekData[] = [
                            'kode_efek' => $m[1],
                            'nama_efek' => trim($m[2]),
                            'bobot' => $bobot,
                            'harga' => $harga,
                        ];
                    }
                    continue;
                }

                // "Bank Central Asia (BBCA) 10.50" or "Bank Central Asia (BBCA) 10.50 9250"
                if (preg_match('/^([A-Za-z\s,.]+)\s*\(([A-Z]{2,6})\)\s+([\d.,]+)\s*%?\s*(?:([\d.,]+))?\s*(?:([\d.,]+))?$/', $line, $m)) {
                    $bobot = (float) str_replace(',', '.', $m[3]);
                    if ($bobot > 0 && $bobot <= 100) {
                        $harga = !empty($m[4]) ? (float) str_replace(',', '.', $m[4]) : null;
                        $efekData[] = [
                            'kode_efek' => $m[2],
                            'nama_efek' => trim($m[1]),
                            'bobot' => $bobot,
                            'harga' => $harga,
                        ];
                    }
                    continue;
                }

                if (count($efekData) >= 30) break;
            }
        }

        return $efekData;
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

        foreach ($lines as $i => $line) {
            $lower = strtolower($line);

            if (str_contains($lower, 'obligasi') || str_contains($lower, 'bond') || str_contains($lower, 'sukuk')) {
                if (preg_match('/^([A-Z0-9]+)\s+(.+?)\s+([\d.,]+)\s+([\d.,]+)\s+([A-Z+]+)/', $line, $m)) {
                    $obligasiData[] = [
                        'kode_obligasi' => $m[1],
                        'nama_obligasi' => trim($m[2]),
                        'bobot' => (float) str_replace(',', '.', $m[3]),
                        'durasi' => (float) str_replace(',', '.', $m[4]),
                        'rating' => $m[5],
                    ];
                }
            }
        }

        return $obligasiData;
    }

    private function extractSukuk(array $lines, string $fullText): array
    {
        $sukukData = [];
        $sukukKeywords = ['sukuk', 'sbsn', 'surat berharga syariah negara', 'sukuk ritel',
                          'project based sukuk', 'pbs', 'sr', 'st'];

        foreach ($lines as $i => $line) {
            $lower = strtolower($line);

            $isSukukLine = false;
            foreach ($sukukKeywords as $keyword) {
                if (str_contains($lower, $keyword)) {
                    $isSukukLine = true;
                    break;
                }
            }

            if ($isSukukLine) {
                // Format: KODE NAMA BOBOT YIELD JATUH_TEMPO RATING
                if (preg_match('/^([A-Z0-9]+)\s+(.+?)\s+([\d.,]+)\s+([\d.,]+)\s+(\d{4})\s+([A-Z+]+)/', $line, $m)) {
                    $sukukData[] = [
                        'kode_sukuk'  => $m[1],
                        'nama_sukuk'  => trim($m[2]),
                        'bobot'       => (float) str_replace(',', '.', $m[3]),
                        'yield'       => (float) str_replace(',', '.', $m[4]),
                        'jatuh_tempo' => $m[5],
                        'rating'      => $m[6],
                    ];
                }
            }
        }

        return $sukukData;
    }

    private function extractBank(array $lines, string $fullText): array
    {
        $bankData = [];

        foreach ($lines as $i => $line) {
            $lower = strtolower($line);

            if (str_contains($lower, 'bank') && !str_contains($lower, 'obligasi') && !str_contains($lower, 'sektor')) {
                if (preg_match('/bank\s+(.+?)\s+([\d.,]+)\s+([\d.,]+)\s+([\d.,]+)\s+(\w+)/i', $line, $m)) {
                    $bankData[] = [
                        'nama_bank' => 'Bank ' . trim($m[1]),
                        'bobot' => (float) str_replace(',', '.', $m[2]),
                        'car' => (float) str_replace(',', '.', $m[3]),
                        'npl' => (float) str_replace(',', '.', $m[4]),
                        'klasifikasi_risiko' => $m[5],
                    ];
                }
            }
        }

        return $bankData;
    }
}
