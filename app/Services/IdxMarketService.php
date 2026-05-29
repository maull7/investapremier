<?php

namespace App\Services;

use App\Models\EffectSector;
use App\Models\StockPrice;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service untuk lookup data saham Indonesia.
 *
 * Sumber data:
 * - Sektor: Yahoo Finance Search API (v1/finance/search)
 * - Harga & Return: Yahoo Finance Chart API (v8/finance/chart)
 * - Kontribusi IHSG: TIDAK tersedia dari API publik, return null
 *
 * Catatan: IDX (www.idx.co.id) menggunakan Cloudflare yang memblokir
 * akses non-browser. Semua endpoint /primary/ IDX tidak bisa diakses
 * langsung dari server. Yahoo Finance digunakan sebagai alternatif.
 *
 * Data yang sudah di-fetch di-cache ke database lokal (effect_sectors,
 * stock_prices) untuk mempercepat akses berikutnya.
 */
class IdxMarketService
{
    protected string $yahooBase;

    protected int $timeout;

    public function __construct()
    {
        $this->yahooBase = 'https://query1.finance.yahoo.com';
        $this->timeout = (int) config('idx.timeout', 15);
    }

    /**
     * Cari sektor saham dari kode efek.
     * Prioritas: database lokal (effect_sectors) -> Yahoo Finance -> null.
     */
    public function getStockSector(string $stockCode): ?string
    {
        $stockCode = strtoupper(trim($stockCode));
        if ($stockCode === '') return null;

        $local = EffectSector::where('effect_code', $stockCode)
            ->where('effect_type', 'Saham')
            ->first();
        if ($local && $local->sector_name) {
            return $local->sector_name;
        }

        try {
            $sector = $this->fetchSectorFromYahoo($stockCode);
            if ($sector) {
                EffectSector::updateOrCreate(
                    ['effect_code' => $stockCode, 'effect_type' => 'Saham'],
                    ['sector_name' => $sector, 'source' => 'Yahoo']
                );
                return $sector;
            }
        } catch (\Throwable $e) {
            Log::warning('IdxMarketService::getStockSector gagal: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Cari kontribusi IHSG (dalam %).
     *
     * Data ini spesifik dari IDX dan tidak tersedia di Yahoo Finance.
     * Selalu return null — user bisa input manual.
     */
    public function getIHSGContribution(string $stockCode, string $date): ?float
    {
        return null;
    }

    /**
     * Dapatkan harga penutupan bulanan untuk kode saham pada tanggal tertentu.
     */
    public function getMonthlyClosePrice(string $stockCode, string $date): ?float
    {
        $stockCode = strtoupper(trim($stockCode));
        if ($stockCode === '' || $date === '') return null;

        try {
            $price = StockPrice::where('kode_efek', $stockCode)
                ->whereDate('tanggal', '<=', $date)
                ->orderBy('tanggal', 'desc')
                ->value('harga');
            if ($price && (float) $price > 0) return (float) $price;

            return $this->fetchClosePriceFromYahoo($stockCode, $date);
        } catch (\Throwable $e) {
            Log::warning('IdxMarketService::getMonthlyClosePrice gagal: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Hitung return saham untuk periode tertentu.
     * Formula: (harga_akhir - harga_awal) / harga_awal
     */
    public function getStockReturn(string $stockCode, string $date, int $months): ?float
    {
        $stockCode = strtoupper(trim($stockCode));
        if ($stockCode === '' || $date === '') return null;

        try {
            $currentDate = \Carbon\Carbon::parse($date);
            $pastDate = $currentDate->copy()->subMonths($months);

            $currentPrice = $this->getMonthlyClosePrice($stockCode, $currentDate->format('Y-m-d'));
            $pastPrice = $this->getMonthlyClosePrice($stockCode, $pastDate->format('Y-m-d'));

            if ($currentPrice === null || $pastPrice === null || $pastPrice == 0) {
                return null;
            }

            return round(($currentPrice - $pastPrice) / $pastPrice * 100, 4);
        } catch (\Throwable $e) {
            Log::warning('IdxMarketService::getStockReturn gagal: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Dapatkan data konstituen indeks.
     */
    public function getIndexConstituentData(string $date): array
    {
        return [];
    }

    // ------------------------------------------------------------------
    // Yahoo Finance API Methods
    // ------------------------------------------------------------------

    /**
     * Fetch sektor saham dari Yahoo Finance Search API.
     * Dengan retry logic untuk rate limiting (429).
     */
    protected function fetchSectorFromYahoo(string $stockCode): ?string
    {
        $symbol = $stockCode . '.JK';
        $url = $this->yahooBase . '/v1/finance/search';

        $response = $this->yahooGet($url, [
            'q'           => $symbol,
            'quotesCount' => 5,
            'newsCount'   => 0,
        ]);

        if ($response && $response->successful()) {
            $data = $response->json();
            $quotes = $data['quotes'] ?? [];
            foreach ($quotes as $quote) {
                if (strtoupper($quote['symbol'] ?? '') === $symbol) {
                    return $quote['sector'] ?? $quote['sectorDisp'] ?? null;
                }
            }
        }

        return null;
    }

    /**
     * Fetch harga penutupan dari Yahoo Finance Chart API.
     * Dengan retry logic untuk rate limiting (429).
     */
    protected function fetchClosePriceFromYahoo(string $stockCode, string $date): ?float
    {
        $symbol = $stockCode . '.JK';
        $targetDate = \Carbon\Carbon::parse($date);

        $daysBack = min($targetDate->diffInDays(\Carbon\Carbon::parse($targetDate)->subMonths(13)), 400);
        $range = max($daysBack, 30);

        $url = $this->yahooBase . '/v8/finance/chart/' . $symbol;

        $response = $this->yahooGet($url, [
            'range'    => $range . 'd',
            'interval' => '1d',
        ]);

        if ($response && $response->successful()) {
            $data = $response->json();
            $result = $data['chart']['result'][0] ?? null;
            if (!$result) return null;

            $timestamps = $result['timestamp'] ?? [];
            $quotes = $result['indicators']['quote'][0] ?? [];
            $closes = $quotes['close'] ?? [];

            $targetMonth = $targetDate->format('Y-m');
            $closestPrice = null;
            $closestDiff = PHP_INT_MAX;

            foreach ($timestamps as $idx => $ts) {
                $itemDate = \Carbon\Carbon::createFromTimestamp($ts);
                if ($itemDate->format('Y-m') === $targetMonth) {
                    $price = $closes[$idx] ?? null;
                    if ($price !== null && (float) $price > 0) {
                        $diff = abs($itemDate->diffInDays($targetDate));
                        if ($diff < $closestDiff) {
                            $closestDiff = $diff;
                            $closestPrice = (float) $price;

                            try {
                                StockPrice::updateOrCreate(
                                    ['kode_efek' => $stockCode, 'tanggal' => $itemDate->format('Y-m-d')],
                                    ['harga' => $price]
                                );
                            } catch (\Throwable $e) {
                                // skip DB error, return value anyway
                            }
                        }
                    }
                }
            }

            if ($closestPrice !== null) {
                return $closestPrice;
            }

            foreach (array_reverse(array_filter($closes, fn($v) => $v !== null)) as $price) {
                return (float) $price;
            }
        }

        return null;
    }

    /**
     * HTTP GET ke Yahoo Finance dengan retry untuk rate limiting (429).
     * Retry maksimal 3x dengan delay exponential backoff.
     */
    protected function yahooGet(string $url, array $params = [], int $maxRetries = 3): ?\Illuminate\Http\Client\Response
    {
        $headers = [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Accept'     => 'application/json, text/plain, */*',
            'Accept-Language' => 'en-US,en;q=0.9',
        ];

        for ($attempt = 0; $attempt < $maxRetries; $attempt++) {
            try {
                $response = Http::withHeaders($headers)
                    ->timeout($this->timeout)
                    ->get($url, $params);

                $status = $response->status();

                if ($status === 429) {
                    $wait = ($attempt + 1) * 2;
                    Log::info("IdxMarketService: rate limited (429), retry in {$wait}s (attempt " . ($attempt + 1) . ")");
                    sleep($wait);
                    continue;
                }

                return $response;
            } catch (\Throwable $e) {
                Log::warning("IdxMarketService: request gagal (attempt " . ($attempt + 1) . "): " . $e->getMessage());
                if ($attempt < $maxRetries - 1) {
                    sleep(($attempt + 1) * 2);
                }
            }
        }

        return null;
    }
}
