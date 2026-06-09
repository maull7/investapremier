<?php

namespace App\Services\Extractors;

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
            $kode = strtoupper(trim($row['Code'] ?? $row['code'] ?? $row['Kode'] ?? $row['kode'] ?? ''));
            if (!$kode) continue;

            $mapped[] = [
                'kode' => $kode,
                'nama' => $row['Name'] ?? $row['name'] ?? $row['Nama'] ?? $row['nama'] ?? '',
                'emiten' => $row['Emiten'] ?? $row['emiten'] ?? $row['Issuer'] ?? $row['issuer'] ?? null,
                'rating' => $row['Rating'] ?? $row['rating'] ?? null,
                'kupon' => $row['Kupon'] ?? $row['kupon'] ?? $row['Coupon'] ?? $row['coupon'] ?? null,
                'jatuh_tempo' => $row['Maturity'] ?? $row['maturity'] ?? $row['JatuhTempo'] ?? $row['jatuh_tempo'] ?? null,
                'harga_persen' => $row['Price'] ?? $row['price'] ?? $row['Harga'] ?? $row['harga_persen'] ?? null,
                'ytm' => $row['YTM'] ?? $row['ytm'] ?? null,
                'current_yield' => $row['CurrentYield'] ?? $row['current_yield'] ?? null,
                'syariah' => $row['Syariah'] ?? $row['syariah'] ?? $row['Sharia'] ?? $row['sharia'] ?? false,
                'denominasi' => $row['Denominasi'] ?? $row['denominasi'] ?? $row['Currency'] ?? $row['currency'] ?? 'IDR',
            ];
        }
        return $mapped;
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
}
