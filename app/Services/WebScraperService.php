<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Scrape atau download konten dari URL:
 * - HTML biasa → ekstrak tabel & teks
 * - CSV/XLS → download lalu parse via WebDataFileParserService
 * - PDF → download lalu parse via FfsParserService
 */
class WebScraperService
{
    public function __construct(
        protected WebDataFileParserService $fileParser,
        protected FfsParserService $pdfParser,
    ) {}

    /**
     * Fetch URL dan kembalikan data terstruktur sesuai tipe konten.
     *
     * @return array{type: string, data: array, message: string}
     */
    public function scrapeUrl(string $url): array
    {
        $response = Http::timeout(60)
            ->withOptions(['allow_redirects' => true])
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (compatible; InvestaPremier/1.0)',
                'Accept' => 'text/html,application/xhtml+xml,text/csv,application/pdf,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,*/*',
            ])
            ->get($url);

        if (!$response->successful()) {
            throw new \RuntimeException('Gagal mengakses URL (HTTP ' . $response->status() . ').');
        }

        $contentType = strtolower($response->header('Content-Type') ?? '');
        $body = $response->body();

        // PDF
        if ($this->isPdf($url, $contentType, $body)) {
            return $this->handlePdf($body, $url);
        }

        // CSV / XLS / XLSX
        if ($this->isSpreadsheet($url, $contentType)) {
            return $this->handleSpreadsheet($body, $url, $contentType);
        }

        // HTML
        if ($this->isHtml($contentType, $body)) {
            return $this->handleHtml($body, $url);
        }

        // Fallback: coba sebagai CSV
        return $this->handleSpreadsheet($body, $url, 'text/csv');
    }

    protected function isPdf(string $url, string $contentType, string $body): bool
    {
        return str_contains($contentType, 'pdf')
            || str_ends_with(strtolower(parse_url($url, PHP_URL_PATH) ?? ''), '.pdf')
            || str_starts_with($body, '%PDF');
    }

    protected function isSpreadsheet(string $url, string $contentType): bool
    {
        $urlLower = strtolower(parse_url($url, PHP_URL_PATH) ?? '');

        return str_contains($contentType, 'csv')
            || str_contains($contentType, 'excel')
            || str_contains($contentType, 'spreadsheetml')
            || str_ends_with($urlLower, '.csv')
            || str_ends_with($urlLower, '.xls')
            || str_ends_with($urlLower, '.xlsx');
    }

    protected function isHtml(string $contentType, string $body): bool
    {
        if (str_contains($contentType, 'html')) {
            return true;
        }
        $start = strtolower(ltrim(substr($body, 0, 200)));

        return str_starts_with($start, '<!doctype')
            || str_starts_with($start, '<html')
            || str_contains($start, '<body');
    }

    protected function handlePdf(string $body, string $url): array
    {
        $tmpPath = $this->writeTempFile($body, 'pdf');

        try {
            $data = $this->pdfParser->parse($tmpPath);
        } finally {
            @unlink($tmpPath);
        }

        $extracted = array_filter([
            $data['nama_reksa_dana'] ? 'Nama RD' : null,
            $data['total_aum'] ? 'Total AUM' : null,
            $data['sektor'] ? count($data['sektor']) . ' Sektor' : null,
            $data['efek'] ? count($data['efek']) . ' Efek' : null,
            $data['kinerja'] ? count($data['kinerja']) . ' Kinerja' : null,
            $data['obligasi'] ? count($data['obligasi']) . ' Obligasi' : null,
            $data['bank'] ? count($data['bank']) . ' Bank' : null,
        ]);

        return [
            'type' => 'pdf',
            'data' => $data,
            'message' => $extracted
                ? 'PDF berhasil diproses: ' . implode(', ', $extracted) . '.'
                : 'PDF terbaca tapi tidak ada data yang cocok (format tidak didukung).',
        ];
    }

    protected function handleSpreadsheet(string $body, string $url, string $contentType): array
    {
        $ext = $this->guessSpreadsheetExt($url, $contentType);
        $tmpPath = $this->writeTempFile($body, $ext);

        try {
            $data = $this->fileParser->parse($tmpPath);
        } finally {
            @unlink($tmpPath);
        }

        $extracted = array_filter([
            $data['nama_reksa_dana'] ? 'Nama RD' : null,
            $data['total_aum'] ? 'Total AUM' : null,
            $data['sektor'] ? count($data['sektor']) . ' Sektor' : null,
            $data['efek'] ? count($data['efek']) . ' Efek' : null,
            $data['kinerja'] ? count($data['kinerja']) . ' Kinerja' : null,
            $data['obligasi'] ? count($data['obligasi']) . ' Obligasi' : null,
            $data['bank'] ? count($data['bank']) . ' Bank' : null,
        ]);

        return [
            'type' => 'spreadsheet',
            'data' => $data,
            'message' => $extracted
                ? 'File berhasil diproses: ' . implode(', ', $extracted) . '.'
                : 'File terbaca tapi tidak ada data yang cocok.',
        ];
    }

    protected function handleHtml(string $body, string $url): array
    {
        $data = $this->extractFromHtml($body);

        $extracted = array_filter([
            $data['nama_reksa_dana'] ? 'Nama RD' : null,
            $data['total_aum'] ? 'Total AUM' : null,
            $data['sektor'] ? count($data['sektor']) . ' Sektor' : null,
            $data['efek'] ? count($data['efek']) . ' Efek' : null,
            $data['kinerja'] ? count($data['kinerja']) . ' Kinerja' : null,
            $data['obligasi'] ? count($data['obligasi']) . ' Obligasi' : null,
            $data['bank'] ? count($data['bank']) . ' Bank' : null,
        ]);

        return [
            'type' => 'html',
            'data' => $data,
            'message' => $extracted
                ? 'HTML berhasil di-scrape: ' . implode(', ', $extracted) . '.'
                : 'Halaman terbaca tapi tidak ada data tabel yang cocok. Data mentah tersedia.',
            'raw_tables' => $data['_raw_tables'] ?? [],
        ];
    }

    /**
     * Ekstrak data dari HTML menggunakan DOMDocument.
     * Mencari tabel dan teks yang relevan dengan analisa reksa dana.
     */
    protected function extractFromHtml(string $html): array
    {
        $data = [
            'nama_reksa_dana' => null,
            'jenis_reksa_dana' => null,
            'total_aum' => null,
            'total_marcap_10_efek' => null,
            'sektor' => [],
            'efek' => [],
            'kinerja' => [],
            'obligasi' => [],
            'bank' => [],
            '_raw_tables' => [],
        ];

        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_NOWARNING | LIBXML_NOERROR);
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);

        // Cari nama reksa dana dari title atau heading
        $this->extractMetaFromHtml($xpath, $data);

        // Ekstrak semua tabel
        $tables = $xpath->query('//table');
        $rawTables = [];

        foreach ($tables as $tableIdx => $table) {
            $tableData = $this->parseHtmlTable($table);
            if (empty($tableData)) {
                continue;
            }

            $rawTables[] = $tableData;

            // Coba klasifikasikan tabel
            $this->classifyAndMergeTable($tableData, $data);
        }

        $data['_raw_tables'] = $rawTables;

        return $data;
    }

    protected function extractMetaFromHtml(\DOMXPath $xpath, array &$data): void
    {
        // Cari dari title
        $titles = $xpath->query('//title');
        if ($titles->length > 0) {
            $titleText = trim($titles->item(0)->textContent);
            if ($titleText && strlen($titleText) < 200) {
                $data['nama_reksa_dana'] = $data['nama_reksa_dana'] ?? $titleText;
            }
        }

        // Cari dari h1/h2 yang mengandung kata kunci reksa dana
        $headings = $xpath->query('//h1 | //h2 | //h3');
        foreach ($headings as $h) {
            $text = trim($h->textContent);
            if (preg_match('/reksa\s*dana|reksadana|fund/i', $text) && strlen($text) < 200) {
                $data['nama_reksa_dana'] = $text;
                break;
            }
        }

        // Cari AUM dari teks
        $allText = $xpath->query('//*[contains(translate(text(), "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"), "aum") or contains(translate(text(), "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"), "dana kelolaan")]');
        foreach ($allText as $node) {
            $text = trim($node->textContent);
            if (preg_match('/[\d.,]+\s*(triliun|miliar|juta|T|M|B)/i', $text, $m)) {
                $data['total_aum'] = $data['total_aum'] ?? $this->parseNumber($m[0]);
                break;
            }
        }
    }

    protected function parseHtmlTable(\DOMElement $table): array
    {
        $rows = [];
        $xpath = new \DOMXPath($table->ownerDocument);

        $trNodes = $xpath->query('.//tr', $table);
        foreach ($trNodes as $tr) {
            $cells = [];
            $tdNodes = $xpath->query('.//td | .//th', $tr);
            foreach ($tdNodes as $td) {
                $cells[] = trim($td->textContent);
            }
            if (array_filter($cells)) {
                $rows[] = $cells;
            }
        }

        return $rows;
    }

    protected function classifyAndMergeTable(array $tableData, array &$data): void
    {
        if (count($tableData) < 2) {
            return;
        }

        $header = array_map('strtolower', $tableData[0]);
        $headerStr = implode(' ', $header);

        // Deteksi tipe tabel berdasarkan header
        if ($this->headerMatches($header, ['sektor', 'sector'])) {
            foreach (array_slice($tableData, 1) as $row) {
                $nama = $row[0] ?? null;
                $bobot = $row[1] ?? null;
                if ($nama) {
                    $data['sektor'][] = ['nama_sektor' => $nama, 'bobot' => $this->parseNumber($bobot)];
                }
            }
        } elseif ($this->headerMatches($header, ['kode', 'efek', 'saham', 'stock', 'equity'])) {
            foreach (array_slice($tableData, 1) as $row) {
                $kode = $row[0] ?? null;
                $nama = $row[1] ?? $row[0] ?? null;
                if ($kode || $nama) {
                    $data['efek'][] = [
                        'kode_efek' => $kode ?? '',
                        'nama_efek' => $nama ?? '',
                        'sektor' => $row[2] ?? '',
                        'bobot' => $this->parseNumber($row[3] ?? $row[2] ?? null),
                        'kontribusi_kinerja' => '',
                        'market_cap' => '',
                        'top_10' => false,
                    ];
                }
            }
        } elseif ($this->headerMatches($header, ['obligasi', 'bond', 'surat utang'])) {
            foreach (array_slice($tableData, 1) as $row) {
                $kode = $row[0] ?? null;
                $nama = $row[1] ?? $row[0] ?? null;
                if ($kode || $nama) {
                    $data['obligasi'][] = [
                        'kode_obligasi' => $kode ?? '',
                        'nama_obligasi' => $nama ?? '',
                        'bobot' => $this->parseNumber($row[2] ?? null),
                        'durasi' => '',
                        'rating' => $row[3] ?? '',
                    ];
                }
            }
        } elseif ($this->headerMatches($header, ['kinerja', 'return', 'nav', 'nab', 'periode', 'bulan'])) {
            foreach (array_slice($tableData, 1) as $row) {
                $periode = $row[0] ?? null;
                $ret = $row[1] ?? null;
                if ($periode) {
                    $data['kinerja'][] = [
                        'periode' => $periode,
                        'return_pct' => $this->parseNumber($ret),
                    ];
                }
            }
        } elseif ($this->headerMatches($header, ['bank', 'deposito'])) {
            foreach (array_slice($tableData, 1) as $row) {
                $nama = $row[0] ?? null;
                if ($nama) {
                    $data['bank'][] = [
                        'nama_bank' => $nama,
                        'bobot' => $this->parseNumber($row[1] ?? null),
                        'car' => '',
                        'npl' => '',
                        'klasifikasi_risiko' => '',
                    ];
                }
            }
        } elseif (count($tableData[0]) === 2) {
            // Tabel key-value — cari AUM, nama RD, dll.
            foreach ($tableData as $row) {
                $key = strtolower(trim($row[0] ?? ''));
                $val = trim($row[1] ?? '');
                if (!$val) {
                    continue;
                }
                if (str_contains($key, 'nama') && str_contains($key, 'reksa')) {
                    $data['nama_reksa_dana'] = $data['nama_reksa_dana'] ?? $val;
                } elseif (str_contains($key, 'aum') || str_contains($key, 'dana kelolaan')) {
                    $data['total_aum'] = $data['total_aum'] ?? $this->parseNumber($val);
                } elseif (str_contains($key, 'jenis')) {
                    $data['jenis_reksa_dana'] = $data['jenis_reksa_dana'] ?? $val;
                }
            }
        }
    }

    protected function headerMatches(array $header, array $keywords): bool
    {
        $headerStr = implode(' ', $header);
        foreach ($keywords as $kw) {
            if (str_contains($headerStr, $kw)) {
                return true;
            }
        }

        return false;
    }

    protected function parseNumber(?string $val): string
    {
        if ($val === null || $val === '') {
            return '';
        }
        // Hapus karakter non-numerik kecuali titik dan koma
        $clean = preg_replace('/[^\d.,\-]/', '', $val);
        // Ganti koma desimal Indonesia ke titik
        $clean = str_replace(',', '.', $clean);

        return $clean;
    }

    protected function writeTempFile(string $body, string $ext): string
    {
        $path = sys_get_temp_dir() . '/scrape-' . uniqid() . '.' . $ext;
        file_put_contents($path, $body);

        return $path;
    }

    protected function guessSpreadsheetExt(string $url, string $contentType): string
    {
        $urlLower = strtolower(parse_url($url, PHP_URL_PATH) ?? '');
        if (str_ends_with($urlLower, '.xlsx') || str_contains($contentType, 'spreadsheetml')) {
            return 'xlsx';
        }
        if (str_ends_with($urlLower, '.xls') || str_contains($contentType, 'ms-excel')) {
            return 'xls';
        }

        return 'csv';
    }
}
