<?php

namespace App\Services;

use App\Models\Stock;
use App\Models\StockPrice;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class YahooStockDataService
{
    private array $baseUrls = [
        'https://query1.finance.yahoo.com',
        'https://query2.finance.yahoo.com',
    ];

    public function syncPrices(Stock $stock, string $range = '1y'): array
    {
        $symbol = $this->symbol($stock->kode);
        $result = $this->fetchChart($symbol, $range);

        $timestamps = $result['timestamp'] ?? [];
        $quotes = $result['indicators']['quote'][0] ?? [];

        $opens = $quotes['open'] ?? [];
        $highs = $quotes['high'] ?? [];
        $lows = $quotes['low'] ?? [];
        $closes = $quotes['close'] ?? [];
        $volumes = $quotes['volume'] ?? [];

        $saved = 0;
        $latest = null;
        $previous = null;

        foreach ($timestamps as $index => $timestamp) {
            $close = $closes[$index] ?? null;
            if ($close === null) {
                continue;
            }

            $date = Carbon::createFromTimestamp($timestamp)->timezone(config('app.timezone'))->toDateString();
            $open = $opens[$index] ?? $close;
            $high = $highs[$index] ?? $close;
            $low = $lows[$index] ?? $close;
            $volume = $volumes[$index] ?? null;

            StockPrice::updateOrCreate(
                [
                    'kode_efek' => strtoupper($stock->kode),
                    'tanggal' => $date,
                ],
                [
                    'stock_id' => $stock->id,
                    'nama_efek' => $stock->nama,
                    'jenis' => 'Saham',
                    'harga' => $close,
                    'open' => $open,
                    'high' => $high,
                    'low' => $low,
                    'close' => $close,
                    'volume' => $volume,
                    'sumber' => 'Yahoo Finance',
                ]
            );

            $previous = $latest;
            $latest = [
                'date' => $date,
                'open' => $open,
                'high' => $high,
                'low' => $low,
                'close' => $close,
                'volume' => $volume,
            ];
            $saved++;
        }

        if ($latest) {
            $stock->update([
                'harga_terbaru' => $latest['close'],
                'harga_penutupan_sebelumnya' => $previous['close'] ?? $stock->harga_penutupan_sebelumnya,
                'harga_pembukaan' => $latest['open'],
                'harga_tertinggi' => $latest['high'],
                'harga_terendah' => $latest['low'],
                'volume' => $latest['volume'],
                'last_update' => $latest['date'],
            ]);
        }

        return [
            'saved' => $saved,
            'symbol' => $symbol,
            'latest_date' => $latest['date'] ?? null,
        ];
    }

    public function fetchYahooData(Stock $stock, string $range = '1d', string $interval = '1m'): array
    {
        $symbol = $this->symbol($stock->kode);
        $intervalMap = [
            '1d' => '1m',
            '5d' => '5m',
            '1mo' => '1h',
            '3mo' => '1d',
            '6mo' => '1d',
            '1y' => '1d',
        ];
        $interval = $intervalMap[$range] ?? '1m';

        $raw = $this->fetchChartFast($symbol, $range, $interval);
        $meta = $raw['meta'] ?? [];
        $timestamps = $raw['timestamp'] ?? [];
        $quotes = $raw['indicators']['quote'][0] ?? [];

        $chartData = [];
        foreach ($timestamps as $i => $ts) {
            $close = $quotes['close'][$i] ?? null;
            if ($close === null) {
                continue;
            }
            $chartData[] = [
                'time' => $ts,
                'open' => $quotes['open'][$i] ?? $close,
                'high' => $quotes['high'][$i] ?? $close,
                'low' => $quotes['low'][$i] ?? $close,
                'close' => $close,
                'volume' => $quotes['volume'][$i] ?? null,
            ];
        }

        return [
            'meta' => $meta,
            'chart' => $chartData,
        ];
    }

    // Single-attempt fetch, no retry/sleep, for live preview
    private function fetchChartFast(string $symbol, string $range, string $interval): array
    {
        foreach ($this->baseUrls as $baseUrl) {
            try {
                $response = Http::withHeaders($this->headers())
                    ->timeout(10)
                    ->get($baseUrl . '/v8/finance/chart/' . $symbol, [
                        'range' => $range,
                        'interval' => $interval,
                        'events' => 'history',
                    ]);

                if ($response->failed()) {
                    continue;
                }

                $data = $response->json();
                if ($data['chart']['error'] ?? null) {
                    continue;
                }

                $result = $data['chart']['result'][0] ?? null;
                if ($result) {
                    return $result;
                }
            } catch (\Throwable $e) {
                continue;
            }
        }

        throw new \RuntimeException('Tidak dapat mengambil data dari Yahoo Finance untuk symbol ' . $symbol . '.');
    }

    private function fetchChartRaw(string $symbol, string $range, string $interval): array
    {
        $lastStatus = null;
        $lastMessage = null;

        foreach ($this->baseUrls as $baseUrl) {
            for ($attempt = 1; $attempt <= 4; $attempt++) {
                try {
                    $response = Http::withHeaders($this->headers())
                        ->timeout((int) config('idx.timeout', 15))
                        ->get($baseUrl . '/v8/finance/chart/' . $symbol, [
                            'range' => $range,
                            'interval' => $interval,
                            'events' => 'history',
                        ]);

                    $lastStatus = $response->status();

                    if ($lastStatus === 429) {
                        $wait = $attempt * 3;
                        Log::info("YahooStockDataService rate limited {$symbol} via {$baseUrl}, retry {$attempt}/4 in {$wait}s.");
                        sleep($wait);
                        continue;
                    }

                    if ($response->failed()) {
                        $lastMessage = 'HTTP ' . $lastStatus;
                        break;
                    }

                    $data = $response->json();
                    $error = $data['chart']['error'] ?? null;
                    if ($error) {
                        $lastMessage = $error['description'] ?? 'Yahoo Finance mengembalikan error.';
                        break;
                    }

                    $result = $data['chart']['result'][0] ?? null;
                    if (!$result) {
                        $lastMessage = 'Data Yahoo Finance tidak tersedia untuk symbol ' . $symbol . '.';
                        break;
                    }

                    return $result;
                } catch (\Throwable $e) {
                    $lastMessage = $e->getMessage();
                    if ($attempt < 4) {
                        sleep($attempt * 2);
                    }
                }
            }
        }

        if ($lastStatus === 429) {
            throw new \RuntimeException('Yahoo Finance sedang rate-limit. Tunggu 1-5 menit lalu coba lagi.');
        }

        throw new \RuntimeException('Yahoo Finance gagal merespons: ' . ($lastMessage ?: 'unknown error'));
    }

    private function fetchChart(string $symbol, string $range): array
    {
        $result = $this->fetchChartRaw($symbol, $this->normalizeRange($range), '1d');

        if (empty($result['timestamp'])) {
            throw new \RuntimeException('Data harga Yahoo Finance tidak tersedia untuk symbol ' . $symbol . '.');
        }

        return $result;
    }

    private function headers(): array
    {
        return [
            'User-Agent' => config('idx.user_agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'),
            'Accept' => 'application/json, text/plain, */*',
            'Accept-Language' => 'en-US,en;q=0.9,id;q=0.8',
            'Cache-Control' => 'no-cache',
            'Pragma' => 'no-cache',
        ];
    }

    private function symbol(string $code): string
    {
        $code = strtoupper(trim($code));

        return str_ends_with($code, '.JK') ? $code : $code . '.JK';
    }

    private function normalizeRange(string $range): string
    {
        return in_array($range, ['5d', '1mo', '3mo', '6mo', 'ytd', '1y', '2y', '5y'], true)
            ? $range
            : '1y';
    }
}
