<?php

namespace App\Services\Extractors;

use App\Models\ObligasiHargaReferensi;
use App\Models\Stock;
use App\Services\GroqService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IdxAiDataExtractorService
{
    private string $playwrightScript;

    public function __construct(
        private GroqService $ai,
    ) {
        $this->playwrightScript = base_path('resources/js/playwright/render-page.mjs');
    }

    public function fetchPageWithPlaywright(string $url): array
    {
        $script = escapeshellarg($this->playwrightScript);
        $escapedUrl = escapeshellarg($url);
        $cmd = "node {$script} {$escapedUrl} 2>/dev/null";

        Log::debug('Playwright: calling node script', ['url' => $url]);
        $output = shell_exec($cmd);
        Log::debug('Playwright: raw output received', ['length' => strlen($output ?? '')]);

        if ($output === null) {
            Log::error('Playwright: process returned no output', ['url' => $url]);
            return [
                'success' => false,
                'blocked' => false,
                'body' => '',
                'status' => 0,
                'content_type' => '',
                'size' => 0,
                'playwright_error' => 'Process returned no output',
            ];
        }

        $result = json_decode($output, true);

        if (!$result || !($result['success'] ?? false)) {
            $error = $result['error'] ?? 'Unknown Playwright error';
            Log::warning('Playwright render failed', ['url' => $url, 'error' => $error]);
            return [
                'success' => false,
                'blocked' => true,
                'body' => '',
                'status' => 0,
                'content_type' => '',
                'size' => 0,
                'playwright_error' => $error,
            ];
        }

        Log::debug('Playwright: success', [
            'has_extracted' => isset($result['extracted']) ? 'YES' : 'NO',
            'extracted_count' => $result['extracted_count'] ?? ($result['extracted'] ? count($result['extracted']) : 0),
            'has_table_data' => isset($result['table_data']) ? 'YES' : 'NO',
            'text_size' => $result['text_size'] ?? 0,
        ]);

        if (isset($result['extracted']) && is_array($result['extracted'])) {
            Log::debug('Playwright: extracted sample', [
                'first' => json_encode($result['extracted'][0] ?? null),
                'last' => json_encode($result['extracted'][count($result['extracted'])-1] ?? null),
            ]);
        }

        return [
            'success' => true,
            'status' => 200,
            'body' => $result['text_content'] ?? '',
            'content_type' => 'text/plain',
            'blocked' => false,
            'size' => $result['text_size'] ?? 0,
            'playwright' => true,
            'extracted_data' => $result['extracted'] ?? null,
            'table_data' => $result['table_data'] ?? null,
        ];
    }

    public function fetchPage(string $url): array
    {
        $response = Http::timeout(30)
            ->withOptions(['allow_redirects' => true])
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7',
                'Referer' => $url,
                'Cache-Control' => 'no-cache',
            ])
            ->get($url);

        $status = $response->status();
        $body = $response->body();
        $contentType = $response->header('Content-Type', '');

        $isBlocked = $status === 403
            || str_contains($body, 'Cloudflare')
            || str_contains($body, 'Attention Required')
            || str_contains($body, 'cf-error-details');

        return [
            'success' => $response->successful() && !$isBlocked,
            'status' => $status,
            'body' => $body,
            'content_type' => $contentType,
            'blocked' => $isBlocked,
            'size' => strlen($body),
        ];
    }

    public function extract(string $url, string $type, ?string $rawContent = null, ?string $mergeUrl = null, bool $skipSectorEnrichment = false): array
    {
        if (!$rawContent) {
            $fetch = $this->fetchPageWithPlaywright($url);

            if (!$fetch['success']) {
                $fetch = $this->fetchPage($url);
            }

            if (!$fetch['success']) {
                return [
                    'success' => false,
                    'blocked' => $fetch['blocked'] ?? true,
                    'message' => ($fetch['blocked'] ?? true)
                        ? 'Website memblokir akses server. Silakan buka halaman di browser, salin HTML/JSON, dan paste di kolom dibawah.'
                        : 'Gagal mengakses URL (HTTP ' . ($fetch['status'] ?? '?') . ').',
                    'data' => [],
                    'fetch_info' => $fetch,
                ];
            }

            // Try to use extracted structured data directly (skip AI)
            $extracted = $fetch['extracted_data'] ?? null;
            if ($extracted && is_array($extracted) && count($extracted) > 0) {
                $mapped = $this->mapExtractedData($extracted, $type);
                if (!empty($mapped)) {
                    return $this->finalizeStockExtract($mapped, $type, $fetch, $mergeUrl, $skipSectorEnrichment, 'data halaman');
                }
            }

            // Try table_data (HTML tables extracted by Playwright from DOM)
            $tableData = $fetch['table_data'] ?? null;
            if ($tableData && is_array($tableData) && count($tableData) > 0) {
                $mapped = $this->mapExtractedData($tableData, $type);
                if (!empty($mapped)) {
                    return $this->finalizeStockExtract($mapped, $type, $fetch, $mergeUrl, $skipSectorEnrichment, 'tabel HTML');
                }
            }
        }

        $rawContent = $rawContent ?? $fetch['body'] ?? '';
        $textContent = $this->stripHtmlForAi($rawContent);

        if ($type === 'obligasi') {
            return $this->extractBonds($url, $textContent);
        }

        return $this->extractStocks($url, $textContent);
    }

    private function mapExtractedData(array $data, string $type): array
    {
        if ($type === 'obligasi') {
            return $this->mapExtractedBonds($data);
        }
        return $this->mapExtractedStocks($data);
    }

    /**
     * Post-process a mapped extraction: optionally enrich sectors, optionally merge a price URL,
     * and return the standard service response payload.
     */
    private function finalizeStockExtract(
        array $mapped,
        string $type,
        array $fetch,
        ?string $mergeUrl,
        bool $skipSectorEnrichment,
        string $sourceLabel
    ): array {
        $label = $type === 'obligasi' ? 'obligasi/sukuk' : 'saham';
        $message = 'Berhasil mengekstrak ' . count($mapped) . ' ' . $label . ' dari ' . $sourceLabel . '.';
        $mergeStats = null;

        if ($type === 'saham') {
            $shouldEnrich = !$skipSectorEnrichment
                && empty($mergeUrl)
                && $this->needsSectorEnrichment($mapped);

            if ($shouldEnrich) {
                $mapped = $this->enrichStockSectors($mapped);
            }

            if ($mergeUrl) {
                [$mapped, $mergeStats] = $this->extractAndMerge($mapped, $mergeUrl, $type, $message);
            }
        }

        return [
            'success' => true,
            'message' => $message,
            'data' => $mapped,
            'fetch_info' => $fetch,
            'merge_stats' => $mergeStats,
        ];
    }

    /**
     * Returns true if enough rows are missing sektor / sub_industri to justify an expensive AI enrichment.
     * If 90%+ rows already have sektor, skip enrichment.
     */
    private function needsSectorEnrichment(array $stocks): bool
    {
        $total = count($stocks);
        if ($total === 0) return false;
        $withSektor = 0;
        foreach ($stocks as $s) {
            if (!empty($s['sektor'])) $withSektor++;
        }
        return ($withSektor / $total) < 0.9;
    }

    private function mapExtractedStocks(array $data): array
    {
        $mapped = [];
        foreach ($data as $row) {
            if (!is_array($row) && !is_object($row)) continue;
            $row = (array) $row;
            $kode = strtoupper(trim(
                $row['Kode Saham'] ?? $row['kode_saham'] ?? $row['KodeSaham'] ?? $row['Code'] ?? $row['code'] ?? $row['Kode'] ?? $row['kode'] ?? ''
            ));
            if (!$kode || strlen($kode) > 5) continue;

            $harga = $this->parseIdrNumber(
                $row['Harga Penutupan'] ?? $row['harga_penutupan'] ?? $row['Penutupan'] ?? $row['penutupan']
                ?? $row['Price'] ?? $row['price'] ?? $row['Harga'] ?? $row['harga']
                ?? $row['harga_terbaru'] ?? null
            );

            $perubahan = $row['Perubahan'] ?? $row['perubahan'] ?? $row['Change'] ?? $row['change'] ?? $row['perubahan_persen'] ?? null;
            if ($perubahan !== null) {
                $perubahan = trim(str_replace('%', '', (string)$perubahan));
                if (is_numeric($perubahan)) {
                    $perubahan = floatval($perubahan);
                }
            }

            $tertinggi = $this->parseIdrNumber(
                $row['Harga Tertinggi'] ?? $row['harga_tertinggi']
                ?? $row['Tertinggi'] ?? $row['tertinggi']
                ?? $row['High'] ?? $row['high'] ?? null
            );

            $terendah = $this->parseIdrNumber(
                $row['Harga Terendah'] ?? $row['harga_terendah']
                ?? $row['Terendah'] ?? $row['terendah']
                ?? $row['Low'] ?? $row['low'] ?? null
            );

            $pembukaan = $this->parseIdrNumber(
                $row['Harga Pembukaan'] ?? $row['harga_pembukaan']
                ?? $row['Pembukaan'] ?? $row['pembukaan']
                ?? $row['OpenPrice'] ?? $row['Open'] ?? $row['open'] ?? null
            );

            $sebelumnya = $this->parseIdrNumber(
                $row['Sebelumnya'] ?? $row['sebelumnya']
                ?? $row['Previous'] ?? $row['previous']
                ?? $row['harga_penutupan_sebelumnya'] ?? null
            );

            $selisih = $this->parseIdrNumber($row['Selisih'] ?? $row['selisih'] ?? null);

            $volume = $this->parseIdrNumber($row['Volume'] ?? $row['volume'] ?? null);
            $nilai = $this->parseIdrNumber($row['Nilai'] ?? $row['nilai'] ?? $row['Value'] ?? $row['value'] ?? null);
            $frekuensi = $this->parseIdrNumber($row['Frekuensi'] ?? $row['frekuensi'] ?? $row['Frequency'] ?? $row['frequency'] ?? null);
            $jumlahSaham = $this->parseIdrNumber(
                $row['Jumlah Saham'] ?? $row['jumlah_saham']
                ?? $row['Saham'] ?? $row['saham']
                ?? $row['Shares'] ?? $row['shares']
                ?? $row['ListedShares'] ?? $row['listed_shares']
                ?? null
            );

            $mapped[] = [
                'kode' => $kode,
                'nama' => trim((string)($row['Nama'] ?? $row['Name'] ?? $row['name'] ?? $row['nama'] ?? '')),
                'sektor' => $row['Sektor'] ?? $row['sektor'] ?? $row['Sector'] ?? $row['sector'] ?? null,
                'sub_industri' => $row['SubSektor'] ?? $row['sub_sektor'] ?? $row['SubSector'] ?? $row['subSector'] ?? $row['sub_sector'] ?? $row['sub_industri'] ?? null,
                'harga_terbaru' => $harga,
                'harga_pembukaan' => $pembukaan,
                'harga_penutupan_sebelumnya' => $sebelumnya,
                'harga_tertinggi' => $tertinggi,
                'harga_terendah' => $terendah,
                'perubahan_persen' => $perubahan,
                'selisih' => $selisih,
                'volume' => $volume,
                'nilai' => $nilai,
                'frekuensi' => $frekuensi,
                'jumlah_saham' => $jumlahSaham,
                'listing_board' => $row['Papan Pencatatan'] ?? $row['papan_pencatatan'] ?? $row['ListingBoard'] ?? $row['listingBoard'] ?? $row['listing_board'] ?? null,
            ];
        }
        return $mapped;
    }

    /**
     * Parse a numeric string in Indonesian format ("10.250,75", "Rp 1.500", "=\n\t\t0"),
     * returning float or null. Strips currency symbols, removes thousand-dot separators,
     * and converts comma decimal to dot.
     */
    private function parseIdrNumber($value): ?float
    {
        if ($value === null || $value === '' || $value === 'null') return null;

        // Native numeric types (int/float from JSON) — return directly without reinterpretation.
        // We deliberately skip this fast-path for strings to avoid misparsing Indonesian
        // thousand-dot formats like "7.450" (which is_numeric() would treat as USA 7.45).
        if (is_int($value) || is_float($value)) return (float) $value;

        $clean = preg_replace('/[\s\=\t\r\n]/', '', (string) $value);
        $clean = preg_replace('/[^\d,\.\-]/', '', $clean);
        if ($clean === '' || $clean === '-' || $clean === '.' || $clean === ',') return null;

        $hasComma = str_contains($clean, ',');
        $hasDot = str_contains($clean, '.');
        if ($hasComma && $hasDot) {
            // Indonesian: dot = thousands, comma = decimal. "10.250,75" -> 10250.75
            $clean = str_replace('.', '', $clean);
            $clean = str_replace(',', '.', $clean);
        } elseif ($hasComma) {
            // Only comma -> decimal. "10,5" -> 10.5
            $clean = str_replace(',', '.', $clean);
        } else {
            // Only dot(s) or no separator. For IDX/DataTable scraping the safe assumption is
            // that any dot is a thousands separator (e.g. "7.450" = 7450, "14.812.900" = 14812900).
            // Decimal-only floats from upstream code paths are already handled by the
            // is_int/is_float fast-path above.
            $clean = str_replace('.', '', $clean);
        }

        return is_numeric($clean) ? (float) $clean : null;
    }

    private function mapExtractedBonds(array $data): array
    {
        $mapped = [];
        foreach ($data as $row) {
            if (!is_array($row) && !is_object($row)) continue;
            $row = (array) $row;

            $kode = strtoupper(trim(
                $row['Bond Code'] ?? $row['BondCode'] ?? $row['bond_code']
                ?? $row['Bond ID'] ?? $row['BondID'] ?? $row['BondId'] ?? $row['bond_id']
                ?? $row['Code'] ?? $row['code']
                ?? $row['Kode'] ?? $row['kode']
                ?? ''
            ));
            if (!$kode) continue;

            $nama = trim((string)(
                $row['Bond Name'] ?? $row['BondName'] ?? $row['bond_name']
                ?? $row['Nama Bond'] ?? $row['NamaBond']
                ?? $row['Name'] ?? $row['name']
                ?? $row['Nama'] ?? $row['nama']
                ?? ''
            ));

            $emiten = $row['Kode Penerbit'] ?? $row['KodePenerbit'] ?? $row['kode_penerbit']
                ?? $row['IssuerCode'] ?? $row['Issuer Code'] ?? $row['issuer_code']
                ?? $row['Emiten'] ?? $row['emiten']
                ?? $row['Issuer'] ?? $row['issuer']
                ?? null;

            $rating = $row['Penilaian'] ?? $row['penilaian']
                ?? $row['Rating'] ?? $row['rating']
                ?? null;

            $kupon = $this->parseIdrNumber(
                $row['Coupon (%)'] ?? $row['Coupon(%)'] ?? $row['Coupon%']
                ?? $row['Kupon'] ?? $row['kupon']
                ?? $row['Coupon'] ?? $row['coupon']
                ?? null
            );

            $jatuhTempoRaw = $row['Maturity Date'] ?? $row['MaturityDate'] ?? $row['maturity_date']
                ?? $row['MatureDate'] ?? $row['Mature Date'] ?? $row['mature_date']
                ?? $row['Jatuh Tempo'] ?? $row['JatuhTempo'] ?? $row['jatuh_tempo']
                ?? $row['Maturity'] ?? $row['maturity']
                ?? null;
            $jatuhTempo = $this->parseBondDate($jatuhTempoRaw);

            $hargaPersen = $this->parseIdrNumber(
                $row['Price'] ?? $row['price'] ?? $row['Harga'] ?? $row['harga'] ?? $row['harga_persen'] ?? null
            );

            $ytm = $this->parseIdrNumber($row['YTM'] ?? $row['ytm'] ?? null);
            $currentYield = $this->parseIdrNumber($row['Current Yield'] ?? $row['CurrentYield'] ?? $row['current_yield'] ?? null);
            $ttm = $this->parseIdrNumber($row['TTM'] ?? $row['ttm'] ?? null);

            $outstanding = $this->parseIdrNumber(
                $row['Outstanding Amount'] ?? $row['OutstandingAmount'] ?? $row['outstanding_amount']
                ?? $row['Outstanding'] ?? $row['outstanding'] ?? $row['OutstandingValue']
                ?? null
            );

            $denominasi = $row['Currency'] ?? $row['currency']
                ?? $row['Denominasi'] ?? $row['denominasi']
                ?? 'IDR';

            $isinCode = $row['ISIN Code'] ?? $row['ISIN'] ?? $row['isin'] ?? $row['isin_code'] ?? null;

            $syariahFlag = $row['Syariah'] ?? $row['syariah'] ?? $row['Sharia'] ?? $row['sharia'] ?? null;
            if ($syariahFlag === null) {
                // Derive from name: "Sukuk" prefix indicates syariah/Islamic bond
                $syariahFlag = (bool) preg_match('/\bsukuk\b/i', $nama);
            } else {
                $syariahFlag = filter_var($syariahFlag, FILTER_VALIDATE_BOOLEAN);
            }

            $mapped[] = [
                'kode' => $kode,
                'nama' => $nama,
                'emiten' => $emiten,
                'rating' => $rating,
                'kupon' => $kupon,
                'jatuh_tempo' => $jatuhTempo,
                'harga_persen' => $hargaPersen,
                'ytm' => $ytm,
                'ttm' => $ttm,
                'current_yield' => $currentYield,
                'outstanding_amount' => $outstanding,
                'syariah' => $syariahFlag,
                'denominasi' => $denominasi,
                'isin_code' => $isinCode,
            ];
        }
        return $mapped;
    }

    /**
     * Parse Indonesian/English date formats commonly seen on IDX/PHEI bond pages:
     *  - "DD-MM-YYYY" (PHEI pemerintah)
     *  - "DD-Mon-YYYY" / "DD Mon YYYY" with Indonesian month names (PHEI korporasi, IDX)
     *  - "YYYY-MM-DD" (ISO)
     * Returns ISO "YYYY-MM-DD" or null when unparseable.
     */
    private function parseBondDate($value): ?string
    {
        if (!$value) return null;
        $raw = trim((string) $value);
        if ($raw === '' || strtolower($raw) === 'null') return null;

        $monthMap = [
            'jan' => '01', 'januari' => '01', 'january' => '01',
            'feb' => '02', 'februari' => '02', 'february' => '02',
            'mar' => '03', 'maret' => '03', 'march' => '03',
            'apr' => '04', 'april' => '04',
            'mei' => '05', 'may' => '05',
            'jun' => '06', 'juni' => '06', 'june' => '06',
            'jul' => '07', 'juli' => '07', 'july' => '07',
            'agt' => '08', 'agu' => '08', 'agust' => '08', 'agustus' => '08', 'aug' => '08', 'august' => '08',
            'sep' => '09', 'sept' => '09', 'september' => '09',
            'okt' => '10', 'oct' => '10', 'oktober' => '10', 'october' => '10',
            'nov' => '11', 'november' => '11',
            'des' => '12', 'dec' => '12', 'desember' => '12', 'december' => '12',
        ];

        // Try ISO YYYY-MM-DD
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $raw, $m)) {
            return "{$m[1]}-{$m[2]}-{$m[3]}";
        }

        // Try DD-MM-YYYY (PHEI pemerintah)
        if (preg_match('/^(\d{1,2})[-\/](\d{1,2})[-\/](\d{4})$/', $raw, $m)) {
            return sprintf('%04d-%02d-%02d', (int) $m[3], (int) $m[2], (int) $m[1]);
        }

        // Try DD-Mon-YYYY or DD Mon YYYY (PHEI korporasi, IDX)
        if (preg_match('/^(\d{1,2})[\s\-\/]+([A-Za-z]+)\.?[\s\-\/]+(\d{4})$/', $raw, $m)) {
            $key = strtolower($m[2]);
            $month = $monthMap[$key] ?? null;
            if ($month) {
                return sprintf('%04d-%s-%02d', (int) $m[3], $month, (int) $m[1]);
            }
        }

        // Last resort: try strtotime
        $ts = strtotime($raw);
        if ($ts !== false) {
            return date('Y-m-d', $ts);
        }

        return null;
    }

    private function extractStocks(string $url, string $textContent): array
    {
        $messages = [
            [
                'role' => 'system',
                'content' => 'Kamu adalah extractor data pasar modal. Ekstrak daftar saham dari konten web. Kembalikan HANYA JSON valid tanpa teks lain.',
            ],
            [
                'role' => 'user',
                'content' => <<<PROMPT
Ekstrak daftar saham dari konten website berikut ({$url}).
Kembalikan array JSON dengan format EXACT:
[
  {
    "kode": "BBCA",
    "nama": "Bank Central Asia Tbk.",
    "sektor": "Keuangan",
    "sub_industri": "Bank",
    "harga_terbaru": 10250.00,
    "perubahan_persen": 0.5
  }
]

ATURAN PENTING:
- kode = kode saham/ticker (2-4 huruf kapital)
- nama = nama perusahaan lengkap
- sektor = sektor industri (jika tidak ada, isi "Lainnya")
- sub_industri = sub industri jika tersedia
- harga_terbaru = harga terakhir dalam Rupiah (angka)
- perubahan_persen = persentase perubahan harga
- Gunakan null untuk data yang tidak tersedia
- Jangan sertakan efek non-saham (warran, right, ETF, REKSADANA, dll) — hanya saham biasa
- Output HANYA JSON array valid, tanpa markdown

KONTEN WEB:
{$textContent}
PROMPT,
            ],
        ];

        return $this->callAiAndParse($messages, 'saham');
    }

    private function extractBonds(string $url, string $textContent): array
    {
        $messages = [
            [
                'role' => 'system',
                'content' => 'Kamu adalah extractor data pasar modal. Ekstrak daftar obligasi/sukuk dari konten web. Kembalikan HANYA JSON valid tanpa teks lain.',
            ],
            [
                'role' => 'user',
                'content' => <<<PROMPT
Ekstrak daftar obligasi dan sukuk dari konten website berikut ({$url}).
Kembalikan array JSON dengan format EXACT:
[
  {
    "kode": "FR0091",
    "nama": "Obligasi Pemerintah FR0091",
    "emiten": "Pemerintah RI",
    "rating": "AAA",
    "kupon": 6.5,
    "jatuh_tempo": "2028-03-15",
    "harga_persen": 101.25,
    "ytm": 6.25,
    "current_yield": 6.35,
    "syariah": false,
    "denominasi": "IDR"
  }
]

ATURAN PENTING:
- kode = kode obligasi/sukuk
- nama = nama lengkap obligasi/sukuk
- emiten = nama perusahaan/penerbit
- rating = peringkat (AAA, AA, A, BBB, dll) atau null
- kupon = tingkat kupon dalam persen (angka)
- jatuh_tempo = tanggal jatuh tempo format YYYY-MM-DD atau null
- harga_persen = harga dalam persen dari nilai nominal
- ytm = yield to maturity dalam persen
- current_yield = current yield dalam persen
- syariah = true jika sukuk/islamic bond, false jika obligasi konvensional
- denominasi = mata uang (IDR, USD, dll) atau null
- Gunakan null untuk data yang tidak tersedia
- Output HANYA JSON array valid, tanpa markdown

KONTEN WEB:
{$textContent}
PROMPT,
            ],
        ];

        return $this->callAiAndParse($messages, 'obligasi/sukuk');
    }

    private function callAiAndParse(array $messages, string $label): array
    {
        $raw = $this->ai->callAi($messages, 120, 0.1);
        $parsed = GroqService::parseJsonOutput($raw);

        if (empty($parsed) || !is_array($parsed)) {
            return [
                'success' => false,
                'message' => 'AI tidak bisa mengekstrak data. Mungkin format halaman tidak dikenali.',
                'data' => [],
                'raw_response' => $raw,
            ];
        }

        return [
            'success' => true,
            'message' => 'Berhasil mengekstrak ' . count($parsed) . ' ' . $label . '.',
            'data' => $parsed,
        ];
    }

    private function enrichStockSectors(array $stocks): array
    {
        $allHaveSector = collect($stocks)->every(fn($s) => !empty($s['sektor']));
        $allHaveSub = collect($stocks)->every(fn($s) => !empty($s['sub_industri']));
        if ($allHaveSector && $allHaveSub) {
            return $stocks;
        }

        $missing = collect($stocks)
            ->filter(fn($s) => empty($s['sektor']) || empty($s['sub_industri']))
            ->values();
        if ($missing->isEmpty()) return $stocks;

        // Process in batches of 200 to avoid AI timeouts
        $batches = $missing->chunk(200);
        $totalFilled = 0;

        foreach ($batches as $batch) {
            $items = $batch->map(fn($s) => $s['kode'] . ': ' . ($s['nama'] ?? $s['kode']));
            $count = $items->count();
            $list = $items->join("\n");
            Log::debug('Enrich sectors: calling AI for ' . $count . ' stocks');

            $prompt = <<<PROMPT
Berikut {$count} saham di BEI. Tebak SEKTOR dan SUB INDUSTRI berdasarkan nama perusahaan.
Gunakan klasifikasi sektor IDX-IC resmi Indonesia.

Kembalikan JSON array:
[{"kode":"AALI","sektor":"Agriculture","sub_industri":"Plantation"},...]

Jika tidak yakin, isi null. Hanya JSON array, tanpa teks lain.

{$list}
PROMPT;

            $messages = [
                ['role' => 'system', 'content' => 'Kamu adalah analis pasar modal Indonesia yang ahli klasifikasi sektor IDX-IC.'],
                ['role' => 'user', 'content' => $prompt],
            ];

            try {
                $raw = $this->ai->callAi($messages, 120, 0.1);
                $parsed = GroqService::parseJsonOutput($raw);
                if (is_array($parsed) && count($parsed) > 0) {
                    $sectors = collect($parsed)->keyBy('kode');
                    $filled = 0;
                    foreach ($stocks as &$stock) {
                        $kode = strtoupper(trim($stock['kode'] ?? ''));
                        if (!$kode || !$sectors->has($kode)) continue;
                        $enrich = $sectors->get($kode);
                        if (empty($stock['sektor']) && !empty($enrich['sektor'])) {
                            $stock['sektor'] = $enrich['sektor'];
                            $filled++;
                        }
                        if (empty($stock['sub_industri']) && !empty($enrich['sub_industri'])) {
                            $stock['sub_industri'] = $enrich['sub_industri'];
                            $filled++;
                        }
                    }
                    unset($stock);
                    $totalFilled += $filled;
                    Log::debug('Enrich sectors: AI filled ' . $filled . ' fields');
                }
            } catch (\Throwable $e) {
                Log::warning('Enrich sectors AI failed: ' . $e->getMessage());
            }
        }

        Log::debug('Enrich sectors: total ' . $totalFilled . ' fields filled across ' . $batches->count() . ' batches');
        return $stocks;
    }

    /**
     * Fetch a secondary URL and merge its price/volume data into the primary list.
     * Returns [mergedData, mergeStats] tuple. Mutates $message in-place to append a short summary.
     *
     * Stats shape:
     *   - primary_count, secondary_count, matched_count, filled_price_count, match_rate
     *   - unmatched_primary_sample, unmatched_secondary_sample (max 10 kodes each)
     *   - error (string, set when secondary fetch fails)
     */
    private function extractAndMerge(array $baseData, string $mergeUrl, string $type, string &$message): array
    {
        Log::debug('Merge: extracting from second URL', ['url' => $mergeUrl]);

        // Always skip sector enrichment for the secondary URL — it's price-focused and would
        // double the AI cost and timeout window.
        $mergeResult = $this->extract($mergeUrl, $type, null, null, true);

        if (!$mergeResult['success'] || empty($mergeResult['data'])) {
            $errorMsg = $mergeResult['message'] ?? 'Gagal mengakses URL harga.';
            $message .= ' (URL harga gagal: ' . $errorMsg . ')';
            Log::warning('Merge: second URL extraction failed', ['url' => $mergeUrl, 'error' => $errorMsg]);
            return [$baseData, [
                'primary_count' => count($baseData),
                'secondary_count' => 0,
                'matched_count' => 0,
                'filled_price_count' => 0,
                'match_rate' => 0.0,
                'unmatched_primary_sample' => [],
                'unmatched_secondary_sample' => [],
                'error' => $errorMsg,
            ]];
        }

        [$merged, $stats] = $this->mergeResults($baseData, $mergeResult['data']);

        $stats['secondary_count'] = count($mergeResult['data']);
        $message .= sprintf(
            ' (%d/%d saham terisi harga dari URL tambahan).',
            $stats['filled_price_count'],
            $stats['primary_count']
        );

        Log::debug('Merge: completed', [
            'primary' => $stats['primary_count'],
            'secondary' => $stats['secondary_count'],
            'matched' => $stats['matched_count'],
            'filled_price' => $stats['filled_price_count'],
            'unmatched_primary_sample' => $stats['unmatched_primary_sample'],
            'unmatched_secondary_sample' => $stats['unmatched_secondary_sample'],
        ]);

        return [$merged, $stats];
    }

    /**
     * Merge secondary (price-focused) rows into primary (identity-focused) rows by `kode`.
     * Secondary is the source of truth for harga/volume/frekuensi/etc — those fields are
     * always overwritten when secondary has a non-empty value.
     *
     * Returns [primaryMerged, stats].
     */
    public function mergeResults(array $primary, array $secondary): array
    {
        $primaryCount = count($primary);
        $secondaryLookup = collect($secondary)->keyBy(fn($s) => strtoupper(trim($s['kode'] ?? '')));
        $primaryCodes = collect($primary)->map(fn($s) => strtoupper(trim($s['kode'] ?? '')))->filter()->flip();

        // Price/trading fields: secondary wins (it's the price source of truth)
        $priceFields = [
            'harga_terbaru', 'harga_pembukaan', 'harga_penutupan_sebelumnya',
            'harga_tertinggi', 'harga_terendah',
            'perubahan_persen', 'selisih',
            'volume', 'nilai', 'frekuensi',
        ];

        $matched = 0;
        $filledPrice = 0;
        $unmatchedPrimary = [];

        foreach ($primary as &$item) {
            $kode = strtoupper(trim($item['kode'] ?? ''));
            if (!$kode) continue;
            if (!$secondaryLookup->has($kode)) {
                if (count($unmatchedPrimary) < 10) $unmatchedPrimary[] = $kode;
                continue;
            }
            $matched++;
            $merge = $secondaryLookup->get($kode);

            foreach ($priceFields as $field) {
                if (isset($merge[$field]) && $merge[$field] !== null && $merge[$field] !== '') {
                    $item[$field] = $merge[$field];
                }
            }

            if (!empty($item['harga_terbaru'])) $filledPrice++;
        }
        unset($item);

        $unmatchedSecondary = [];
        foreach ($secondaryLookup as $kode => $row) {
            if (!$primaryCodes->has($kode)) {
                if (count($unmatchedSecondary) < 10) $unmatchedSecondary[] = $kode;
            }
        }

        $stats = [
            'primary_count' => $primaryCount,
            'secondary_count' => count($secondary),
            'matched_count' => $matched,
            'filled_price_count' => $filledPrice,
            'match_rate' => $primaryCount > 0 ? round($matched / $primaryCount, 4) : 0.0,
            'unmatched_primary_sample' => $unmatchedPrimary,
            'unmatched_secondary_sample' => $unmatchedSecondary,
        ];

        return [$primary, $stats];
    }

    private function stripHtmlForAi(string $html): string
    {
        $text = strip_tags($html);
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim(mb_substr($text, 0, 15000));

        return $text;
    }

    /**
     * Upsert mapped stock items into the `stocks` table.
     * - Insert new codes
     * - Update existing codes' price/volume/listing fields (always overwrite)
     * - When $preserveExistingSector is true, do NOT overwrite sektor/sub_industri
     *   if the existing row already has a non-empty value (protects manual edits).
     * - `nama` is only overwritten when the incoming value is non-empty and different.
     *
     * Returns: ['created' => int, 'updated' => int, 'skipped' => int, 'errors' => string[]]
     */
    public function upsertStocks(array $items, bool $preserveExistingSector = true): array
    {
        $stats = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => []];

        DB::transaction(function () use ($items, $preserveExistingSector, &$stats) {
            foreach ($items as $item) {
                $kode = strtoupper(trim($item['kode'] ?? ''));
                if (!$kode) {
                    $stats['skipped']++;
                    continue;
                }

                try {
                    $existing = Stock::where('kode', $kode)->first();

                    $hargaTerbaru = $this->parseIdrNumber($item['harga_terbaru'] ?? null);
                    $jumlahSaham = $this->parseIdrNumber($item['jumlah_saham'] ?? null);

                    // Fallback: kalau jumlah_saham tidak ada di extraction tapi sudah tersimpan di DB,
                    // tetap pakai existing untuk hitung market cap (data emiten jarang berubah).
                    if (($jumlahSaham === null || $jumlahSaham == 0) && $existing && $existing->jumlah_saham) {
                        $jumlahSaham = (float) $existing->jumlah_saham;
                    }

                    $marketCap = null;
                    if ($jumlahSaham !== null && $jumlahSaham > 0 && $hargaTerbaru !== null && $hargaTerbaru > 0) {
                        $marketCap = $jumlahSaham * $hargaTerbaru;
                    }

                    $payload = [
                        'kode' => $kode,
                        'harga_terbaru' => $hargaTerbaru,
                        'harga_pembukaan' => $this->parseIdrNumber($item['harga_pembukaan'] ?? null),
                        'harga_penutupan_sebelumnya' => $this->parseIdrNumber($item['harga_penutupan_sebelumnya'] ?? null),
                        'harga_tertinggi' => $this->parseIdrNumber($item['harga_tertinggi'] ?? null),
                        'harga_terendah' => $this->parseIdrNumber($item['harga_terendah'] ?? null),
                        'perubahan_persen' => $item['perubahan_persen'] ?? null,
                        'volume' => $this->parseIdrNumber($item['volume'] ?? null),
                        'value' => $this->parseIdrNumber($item['nilai'] ?? null),
                        'frekuensi' => $this->parseIdrNumber($item['frekuensi'] ?? null),
                        'jumlah_saham' => $jumlahSaham,
                        'market_capital' => $marketCap,
                        'listing_board' => $item['listing_board'] ?? null,
                        'last_update' => now(),
                    ];

                    $incomingNama = trim((string)($item['nama'] ?? ''));
                    $incomingSektor = $item['sektor'] ?? null;
                    $incomingSub = $item['sub_industri'] ?? null;

                    if ($existing) {
                        if ($incomingNama !== '' && $incomingNama !== $existing->nama) {
                            $payload['nama'] = $incomingNama;
                        }

                        $shouldOverwriteSektor = !($preserveExistingSector && !empty($existing->sektor));
                        if ($shouldOverwriteSektor && !empty($incomingSektor)) {
                            $payload['sektor'] = $incomingSektor;
                        }

                        $shouldOverwriteSub = !($preserveExistingSector && !empty($existing->sub_industri));
                        if ($shouldOverwriteSub && !empty($incomingSub)) {
                            $payload['sub_industri'] = $incomingSub;
                        }

                        $existing->update($payload);
                        $stats['updated']++;
                    } else {
                        $payload['nama'] = $incomingNama !== '' ? $incomingNama : $kode;
                        if (!empty($incomingSektor)) $payload['sektor'] = $incomingSektor;
                        if (!empty($incomingSub)) $payload['sub_industri'] = $incomingSub;
                        Stock::create($payload);
                        $stats['created']++;
                    }
                } catch (\Throwable $e) {
                    $stats['skipped']++;
                    if (count($stats['errors']) < 10) {
                        $stats['errors'][] = "{$kode}: " . $e->getMessage();
                    }
                    Log::warning('upsertStocks failed for ' . $kode, ['error' => $e->getMessage()]);
                }
            }
        });

        Log::info('upsertStocks completed', $stats);
        return $stats;
    }

    /**
     * Fetch corporate bond rows from the internal IDX API
     * (/secondary/get/BondSukuk/bond?bondType=1) via Playwright (browser context
     * is required to bypass Cloudflare challenges).
     *
     * Returns raw rows with API field names: BondId, BondName, IssuerCode,
     * MatureDate (ISO), Rating, Outstanding, Nomor.
     *
     * @param int $bondType IDX API bondType: 1=corporate (default), 2/3=gov FR, 4=gov ritel ORI
     * @param int $pageSize Max rows to fetch in one API call (~1419 total for corporate)
     */
    public function fetchIdxCorporateBonds(int $bondType = 1, int $pageSize = 2000): array
    {
        $script = base_path('resources/js/playwright/fetch-idx-bonds.mjs');
        if (!file_exists($script)) {
            Log::warning('IDX bonds fetch script missing', ['path' => $script]);
            return [];
        }

        $cmd = 'node ' . escapeshellarg($script)
            . ' ' . escapeshellarg((string) $bondType)
            . ' ' . escapeshellarg((string) $pageSize)
            . ' 2>/dev/null';

        Log::debug('IDX bonds fetch: calling script', ['bond_type' => $bondType, 'page_size' => $pageSize]);
        $output = shell_exec($cmd);
        if (!$output) {
            Log::warning('IDX bonds fetch returned empty output');
            return [];
        }

        $decoded = json_decode($output, true);
        if (!is_array($decoded) || !($decoded['success'] ?? false)) {
            Log::warning('IDX bonds fetch failed', ['error' => $decoded['error'] ?? 'unknown']);
            return [];
        }

        Log::info('IDX bonds fetch completed', [
            'bond_type' => $bondType,
            'total_count' => $decoded['total_count'] ?? 0,
            'returned_count' => $decoded['returned_count'] ?? 0,
        ]);

        return $decoded['rows'] ?? [];
    }

    /**
     * Fetch bond rows from phei.co.id via the dedicated Playwright script.
     * $tab can be: 'pemerintah', 'korporasi', or 'all'.
     *
     * Returns array of raw rows (raw PHEI keys: "Bond Code", "Bond Name", "ISIN Code",
     * "Currency", "Outstanding Amount", "Coupon (%)", "Maturity Date").
     */
    public function fetchPheiBonds(string $tab = 'all'): array
    {
        $script = base_path('resources/js/playwright/fetch-phei-bonds.mjs');
        if (!file_exists($script)) {
            Log::warning('PHEI fetch script missing', ['path' => $script]);
            return [];
        }

        $cmd = 'node ' . escapeshellarg($script) . ' ' . escapeshellarg($tab) . ' 2>/dev/null';
        Log::debug('PHEI fetch: calling script', ['tab' => $tab]);
        $output = shell_exec($cmd);
        if (!$output) {
            Log::warning('PHEI fetch returned empty output', ['tab' => $tab]);
            return [];
        }

        $decoded = json_decode($output, true);
        if (!is_array($decoded) || !($decoded['success'] ?? false)) {
            Log::warning('PHEI fetch failed', ['tab' => $tab, 'error' => $decoded['error'] ?? 'unknown']);
            return [];
        }

        // Flatten rows from all returned tabs into a single list.
        $rows = [];
        foreach (($decoded['tabs'] ?? []) as $tabKey => $info) {
            $tabRows = $info['rows'] ?? [];
            foreach ($tabRows as $r) {
                if (!is_array($r)) continue;
                $r['_phei_tab'] = $tabKey;
                $rows[] = $r;
            }
        }

        Log::info('PHEI fetch completed', [
            'tab' => $tab,
            'total' => count($rows),
            'breakdown' => array_map(fn ($t) => ['count' => $t['count'] ?? 0, 'pages' => $t['pages_traversed'] ?? 0, 'stop' => $t['stopped_reason'] ?? null], $decoded['tabs'] ?? []),
        ]);

        return $rows;
    }

    /**
     * Merge IDX corporate bond rows with PHEI government + corporate rows.
     * Identity key: bond code (uppercase).
     *
     * Priority:
     *  - IDX provides: rating, emiten (and nama)
     *  - PHEI provides: kupon, outstanding_amount, jatuh_tempo, denominasi, isin_code (more authoritative on these)
     *  - Unmatched PHEI codes are added as new entries.
     *
     * Returns: [mergedItems, stats]
     */
    public function mergeBondResults(array $idx, array $pheiGovt, array $pheiCorp): array
    {
        $idxMapped = $this->mapExtractedBonds($idx);
        $pheiGovtMapped = $this->mapExtractedBonds($pheiGovt);
        $pheiCorpMapped = $this->mapExtractedBonds($pheiCorp);

        $byCode = [];
        $sourceTags = [];

        // Helper to merge fields from a "secondary" PHEI row into a primary entry,
        // overwriting only when the secondary value is non-empty.
        $overlayPhei = function (array $primary, array $phei): array {
            $pheiFields = [
                'kupon', 'jatuh_tempo', 'outstanding_amount', 'denominasi',
                'isin_code', 'syariah',
            ];
            foreach ($pheiFields as $f) {
                $val = $phei[$f] ?? null;
                if ($val === null || $val === '' || $val === false && $f !== 'syariah') {
                    // For syariah specifically allow boolean false to overwrite null.
                    if (!($f === 'syariah' && array_key_exists('syariah', $phei))) {
                        continue;
                    }
                }
                if ($f === 'syariah') {
                    // Only set syariah from PHEI when primary didn't already have an explicit true.
                    if (empty($primary['syariah'])) {
                        $primary['syariah'] = (bool) $val;
                    }
                    continue;
                }
                $primary[$f] = $val;
            }
            // PHEI nama is sometimes more complete than IDX; only use if primary missing.
            if (empty($primary['nama']) && !empty($phei['nama'])) {
                $primary['nama'] = $phei['nama'];
            }
            return $primary;
        };

        // 1) Seed with IDX (rating + emiten authoritative)
        foreach ($idxMapped as $row) {
            $code = $row['kode'];
            $byCode[$code] = $row;
            $sourceTags[$code] = ['idx'];
        }

        // 2) Overlay PHEI Pemerintah
        foreach ($pheiGovtMapped as $row) {
            $code = $row['kode'];
            if (isset($byCode[$code])) {
                $byCode[$code] = $overlayPhei($byCode[$code], $row);
                $sourceTags[$code][] = 'phei_govt';
            } else {
                $row['_bond_class'] = 'government';
                $byCode[$code] = $row;
                $sourceTags[$code] = ['phei_govt'];
            }
        }

        // 3) Overlay PHEI Korporasi
        foreach ($pheiCorpMapped as $row) {
            $code = $row['kode'];
            if (isset($byCode[$code])) {
                $byCode[$code] = $overlayPhei($byCode[$code], $row);
                $sourceTags[$code][] = 'phei_corp';
            } else {
                $row['_bond_class'] = 'corporate';
                $byCode[$code] = $row;
                $sourceTags[$code] = ['phei_corp'];
            }
        }

        $merged = array_values($byCode);

        $idxCodes = array_column($idxMapped, 'kode');
        $pheiGovtCodes = array_column($pheiGovtMapped, 'kode');
        $pheiCorpCodes = array_column($pheiCorpMapped, 'kode');
        $allPheiCodes = array_merge($pheiGovtCodes, $pheiCorpCodes);

        $matchedIdxPhei = count(array_intersect($idxCodes, $allPheiCodes));
        $idxOnly = array_values(array_diff($idxCodes, $allPheiCodes));
        $pheiOnly = array_values(array_diff($allPheiCodes, $idxCodes));

        $stats = [
            'idx_count' => count($idxMapped),
            'phei_govt_count' => count($pheiGovtMapped),
            'phei_corp_count' => count($pheiCorpMapped),
            'phei_total' => count($allPheiCodes),
            'merged_count' => count($merged),
            'idx_matched_in_phei' => $matchedIdxPhei,
            'idx_only_count' => count($idxOnly),
            'phei_only_count' => count($pheiOnly),
            'idx_only_sample' => array_slice($idxOnly, 0, 10),
        ];

        Log::info('mergeBondResults completed', $stats);

        return [$merged, $stats];
    }

    /**
     * Upsert merged bond items into the `obligasi_harga_referensi` table.
     *
     * Preserve rules when $preserveExisting = true:
     *  - rating, sektor, sub_sektor, industri, sub_industri: preserve existing non-empty values
     *  - harga_persen, ytm, ttm, current_yield, total_val: NEVER overwrite when source value
     *    is null (these come from free sources that don't provide pricing, so existing manual
     *    entries must be protected)
     *
     * Always-overwrite fields (when source has a value):
     *  - kupon, outstanding_amount, denominasi, jatuh_tempo
     *
     * Returns: ['created' => int, 'updated' => int, 'skipped' => int, 'errors' => string[]]
     */
    public function upsertBonds(array $items, bool $preserveExisting = true): array
    {
        $stats = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => []];

        DB::transaction(function () use ($items, $preserveExisting, &$stats) {
            foreach ($items as $item) {
                $kode = strtoupper(trim($item['kode'] ?? ''));
                if (!$kode) {
                    $stats['skipped']++;
                    continue;
                }

                try {
                    $existing = ObligasiHargaReferensi::where('kode', $kode)->first();

                    // Always-overwrite-when-source-has-value fields (PHEI is authoritative)
                    $payload = [
                        'kode' => $kode,
                    ];

                    $incomingNama = trim((string) ($item['nama'] ?? ''));
                    if ($incomingNama !== '') {
                        // For existing, only update nama if it changed (avoid useless writes)
                        if (!$existing || $existing->nama !== $incomingNama) {
                            $payload['nama'] = $incomingNama;
                        }
                    }

                    $incomingEmiten = $item['emiten'] ?? null;
                    if (!empty($incomingEmiten)) {
                        $payload['emiten'] = $incomingEmiten;
                    }

                    $incomingDenom = $item['denominasi'] ?? null;
                    if (!empty($incomingDenom)) {
                        $payload['denominasi'] = $incomingDenom;
                    }

                    $incomingKupon = $item['kupon'] ?? null;
                    if ($incomingKupon !== null && $incomingKupon !== '') {
                        $payload['kupon'] = $incomingKupon;
                    }

                    $incomingJT = $item['jatuh_tempo'] ?? null;
                    if (!empty($incomingJT)) {
                        $payload['jatuh_tempo'] = $incomingJT;
                    }

                    $incomingOutstanding = $item['outstanding_amount'] ?? null;
                    if ($incomingOutstanding !== null && $incomingOutstanding !== '') {
                        $payload['outstanding_amount'] = $incomingOutstanding;
                    }

                    $incomingSyariah = $item['syariah'] ?? null;
                    if ($incomingSyariah !== null) {
                        $payload['syariah'] = (bool) $incomingSyariah;
                    }

                    // Preserve-existing-when-set fields: rating, sektor, sub_sektor, industri, sub_industri
                    $incomingRating = $item['rating'] ?? null;
                    if (!empty($incomingRating)) {
                        $shouldOverwriteRating = !($preserveExisting && $existing && !empty($existing->rating));
                        if ($shouldOverwriteRating) {
                            $payload['rating'] = $incomingRating;
                        }
                    }

                    // Pricing fields are never set from these free sources, but accept if explicitly provided.
                    foreach (['harga_persen', 'ytm', 'ttm', 'current_yield', 'total_val'] as $priceField) {
                        $val = $item[$priceField] ?? null;
                        if ($val === null || $val === '') {
                            continue;
                        }
                        $payload[$priceField] = $val;
                    }

                    if ($existing) {
                        if (count($payload) > 1) { // kode + at least one other field
                            $existing->update($payload);
                        }
                        $stats['updated']++;
                    } else {
                        if (empty($payload['nama'])) {
                            $payload['nama'] = $kode;
                        }
                        ObligasiHargaReferensi::create($payload);
                        $stats['created']++;
                    }
                } catch (\Throwable $e) {
                    $stats['skipped']++;
                    if (count($stats['errors']) < 10) {
                        $stats['errors'][] = "{$kode}: " . $e->getMessage();
                    }
                    Log::warning('upsertBonds failed for ' . $kode, ['error' => $e->getMessage()]);
                }
            }
        });

        Log::info('upsertBonds completed', $stats);
        return $stats;
    }
}
