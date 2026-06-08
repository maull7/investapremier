<?php

namespace App\Services\Extractors;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class YahooFinanceStockExtractorService implements ExtractorInterface
{
    public function extract(array $parameters): array
    {
        $code = strtoupper(trim((string) ($parameters['identifier'] ?? '')));
        if ($code === '') {
            throw new \InvalidArgumentException('Kode saham wajib diisi untuk sumber Yahoo Finance.');
        }

        $symbol = $this->symbol($code);
        $date = Carbon::parse($parameters['data_date'] ?? now())->startOfDay();
        $period1 = $date->copy()->subDay()->timestamp;
        $period2 = $date->copy()->addDay()->timestamp;

        $response = $this->chartRequest($symbol, [
            'period1' => $period1,
            'period2' => $period2,
            'interval' => '1d',
            'region' => 'ID',
            'events' => 'history',
        ]);

        if ($response->failed()) {
            throw new \RuntimeException('Yahoo Finance gagal diakses: HTTP ' . $response->status());
        }

        $result = $response->json('chart.result.0');
        $timestamps = $result['timestamp'] ?? [];
        $quotes = $result['indicators']['quote'][0] ?? [];

        if (!$result || empty($timestamps)) {
            throw new \RuntimeException("Data harian Yahoo Finance tidak tersedia untuk {$symbol} pada {$date->toDateString()}.");
        }

        $rows = [];
        foreach ($timestamps as $index => $timestamp) {
            $rowDate = Carbon::createFromTimestamp($timestamp)->timezone(config('app.timezone'))->toDateString();
            if ($rowDate !== $date->toDateString()) {
                continue;
            }

            $close = $quotes['close'][$index] ?? null;
            if ($close === null) {
                continue;
            }

            $previousClose = $result['meta']['chartPreviousClose'] ?? $result['meta']['previousClose'] ?? null;
            $priceChange = $previousClose !== null ? $close - $previousClose : null;
            $changePercent = $previousClose ? ($priceChange / $previousClose) * 100 : null;

            $rows[] = [
                'stock_code' => $code,
                'data_date' => $rowDate,
                'open' => $quotes['open'][$index] ?? $close,
                'high' => $quotes['high'][$index] ?? $close,
                'low' => $quotes['low'][$index] ?? $close,
                'close' => $close,
                'volume' => $quotes['volume'][$index] ?? null,
                'price_change' => $priceChange,
                'change_percent' => $changePercent,
                'market_cap' => $result['meta']['marketCap'] ?? null,
                'source' => 'Yahoo Finance',
                'raw_payload' => [
                    'symbol' => $symbol,
                    'meta' => $result['meta'] ?? [],
                ],
            ];
        }

        if ($rows === []) {
            throw new \RuntimeException("Data Yahoo Finance untuk {$symbol} tidak ditemukan pada tanggal {$date->toDateString()}.");
        }

        return $rows;
    }

    private function symbol(string $code): string
    {
        return str_ends_with($code, '.JK') ? $code : $code . '.JK';
    }

    private function chartRequest(string $symbol, array $params)
    {
        if (config('services.yfapi.enabled') && filled(config('services.yfapi.key'))) {
            return Http::withHeaders([
                'accept' => 'application/json',
                'X-API-KEY' => (string) config('services.yfapi.key'),
            ])
                ->retry((int) config('services.extraction.retry', 3), (int) config('services.extraction.retry_sleep_ms', 500))
                ->timeout((int) config('services.extraction.timeout', 20))
                ->get(rtrim((string) config('services.yfapi.url', 'https://yfapi.net'), '/') . "/v8/finance/chart/{$symbol}", $params);
        }

        return Http::withHeaders([
            'accept' => 'application/json',
            'User-Agent' => config('idx.user_agent', 'Mozilla/5.0'),
        ])
            ->retry((int) config('services.extraction.retry', 3), (int) config('services.extraction.retry_sleep_ms', 500))
            ->timeout((int) config('services.extraction.timeout', 20))
            ->get(rtrim((string) config('services.yahoo_finance.chart_url', 'https://query1.finance.yahoo.com/v8/finance/chart'), '/') . "/{$symbol}", $params);
    }
}
