<?php

namespace App\Services;

use App\Models\Stock;
use App\Models\StockPrice;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class YahooStockDataService
{
    private string $baseUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.yfapi.url', 'https://yfapi.net');
        $this->apiKey  = config('services.yfapi.key', '');
    }

    public function fetchSummary(Stock $stock): array
    {
        $symbol   = $this->symbol($stock->kode);
        $cacheKey = "yfapi_summary_v2_{$symbol}";

        return Cache::remember($cacheKey, 3600, function () use ($symbol) {
            $data = $this->apiGet("/v11/finance/quoteSummary/{$symbol}", [
                'lang'    => 'ID',
                'region'  => 'ID',
                'modules' => implode(',', [
                    'assetProfile',
                    'summaryDetail',
                    'defaultKeyStatistics',
                    'financialData',
                    'incomeStatementHistory',
                    'balanceSheetHistory',
                    'cashflowStatementHistory',
                    'recommendationTrend',
                    'upgradeDowngradeHistory',
                ]),
            ]);

            $result = $data['quoteSummary']['result'][0] ?? [];
            if ($result === []) {
                throw new \RuntimeException('Ringkasan saham tidak tersedia untuk symbol ' . $symbol . '.');
            }

            $profile   = $result['assetProfile'] ?? [];
            $stats     = $result['defaultKeyStatistics'] ?? [];
            $detail    = $result['summaryDetail'] ?? [];
            $financial = $result['financialData'] ?? [];

            return [
                'profile' => [
                    'industry'       => $profile['industryDisp'] ?? $profile['industry'] ?? null,
                    'sector'         => $profile['sectorDisp'] ?? $profile['sector'] ?? null,
                    'website'        => $profile['website'] ?? null,
                    'phone'          => $profile['phone'] ?? null,
                    'address'        => trim(implode(', ', array_filter([
                        $profile['address1'] ?? null,
                        $profile['address2'] ?? null,
                        $profile['city'] ?? null,
                        $profile['country'] ?? null,
                    ]))),
                    'employees'      => $profile['fullTimeEmployees'] ?? null,
                    'description'    => $profile['longBusinessSummary'] ?? null,
                ],
                'stats' => [
                    'trailingPE'        => $this->raw($detail, 'trailingPE'),
                    'forwardPE'         => $this->raw($stats, 'forwardPE'),
                    'priceToBook'       => $this->raw($stats, 'priceToBook'),
                    'profitMargins'     => $this->raw($financial, 'profitMargins'),
                    'beta'              => $this->raw($stats, 'beta'),
                    'sharesOutstanding' => $this->raw($stats, 'sharesOutstanding'),
                    'bookValue'         => $this->raw($stats, 'bookValue'),
                    'earningsPerShare'  => $this->raw($stats, 'trailingEps'),
                    'dividendYield'     => $this->raw($detail, 'dividendYield'),
                ],
                'financials' => $this->financialStatements($result),
                'analysts'   => [
                    'targetHighPrice'          => $this->raw($financial, 'targetHighPrice'),
                    'targetLowPrice'           => $this->raw($financial, 'targetLowPrice'),
                    'targetMeanPrice'          => $this->raw($financial, 'targetMeanPrice'),
                    'recommendationMean'       => $this->raw($financial, 'recommendationMean'),
                    'recommendationKey'        => $financial['recommendationKey'] ?? null,
                    'numberOfAnalystOpinions'  => $this->raw($financial, 'numberOfAnalystOpinions'),
                    'trend'                    => $result['recommendationTrend']['trend'] ?? [],
                    'upgradesDowngrades'       => $result['upgradeDowngradeHistory']['history'] ?? [],
                ],
                'news' => $this->fetchNews($symbol),
            ];
        });
    }

    // ─── Public: ambil quote detail + chart untuk ditampilkan di UI ───────────

    public function fetchYahooData(Stock $stock, string $range = '1d'): array
    {
        $symbol   = $this->symbol($stock->kode);
        $interval = $this->intervalFor($range);
        $ttl      = in_array($range, ['1d', '5d']) ? 300 : 3600;
        $cacheKey = "yfapi_chart_{$symbol}_{$range}";

        return Cache::remember($cacheKey, $ttl, function () use ($symbol, $range, $interval) {
            $quote = $this->fetchQuote($symbol);
            $chart = $this->fetchChart($symbol, $range, $interval);

            return ['meta' => $quote, 'chart' => $chart];
        });
    }

    // ─── Public: sync historical prices ke database ───────────────────────────

    public function syncPrices(Stock $stock, string $range = '1y'): array
    {
        $symbol   = $this->symbol($stock->kode);
        $range    = $this->normalizeRange($range);
        $interval = '1d';

        $response = $this->apiGet("/v8/finance/chart/{$symbol}", [
            'range'    => $range,
            'interval' => $interval,
            'region'   => 'ID',
            'events'   => 'history',
        ]);

        $result = $response['chart']['result'][0] ?? null;
        if (!$result || empty($result['timestamp'])) {
            throw new \RuntimeException('Data harga tidak tersedia untuk symbol ' . $symbol . '.');
        }

        $timestamps = $result['timestamp'];
        $quotes     = $result['indicators']['quote'][0] ?? [];
        $opens      = $quotes['open'] ?? [];
        $highs      = $quotes['high'] ?? [];
        $lows       = $quotes['low'] ?? [];
        $closes     = $quotes['close'] ?? [];
        $volumes    = $quotes['volume'] ?? [];

        $saved    = 0;
        $latest   = null;
        $previous = null;

        foreach ($timestamps as $index => $timestamp) {
            $close = $closes[$index] ?? null;
            if ($close === null) continue;

            $date   = Carbon::createFromTimestamp($timestamp)->timezone(config('app.timezone'))->toDateString();
            $open   = $opens[$index] ?? $close;
            $high   = $highs[$index] ?? $close;
            $low    = $lows[$index] ?? $close;
            $volume = $volumes[$index] ?? null;

            StockPrice::updateOrCreate(
                ['kode_efek' => strtoupper($stock->kode), 'tanggal' => $date],
                [
                    'stock_id'   => $stock->id,
                    'nama_efek'  => $stock->nama,
                    'jenis'      => 'Saham',
                    'harga'      => $close,
                    'open'       => $open,
                    'high'       => $high,
                    'low'        => $low,
                    'close'      => $close,
                    'volume'     => $volume,
                    'sumber'     => 'Yahoo Finance',
                ]
            );

            $previous = $latest;
            $latest   = compact('date', 'open', 'high', 'low', 'close', 'volume');
            $saved++;
        }

        if ($latest) {
            $stock->update([
                'harga_terbaru'                  => $latest['close'],
                'harga_penutupan_sebelumnya'     => $previous['close'] ?? $stock->harga_penutupan_sebelumnya,
                'harga_pembukaan'                => $latest['open'],
                'harga_tertinggi'                => $latest['high'],
                'harga_terendah'                 => $latest['low'],
                'volume'                         => $latest['volume'],
                'last_update'                    => $latest['date'],
            ]);
        }

        return ['saved' => $saved, 'symbol' => $symbol, 'latest_date' => $latest['date'] ?? null];
    }

    // ─── Private helpers ──────────────────────────────────────────────────────

    private function fetchQuote(string $symbol): array
    {
        $data = $this->apiGet('/v6/finance/quote', [
            'symbols' => $symbol,
            'region'  => 'ID',
            'lang'    => 'ID-id',
        ]);

        $quote = $data['quoteResponse']['result'][0] ?? [];

        return $quote + [
            'previousClose' => $quote['regularMarketPreviousClose'] ?? null,
        ];
    }

    private function fetchChart(string $symbol, string $range, string $interval): array
    {
        $data = $this->apiGet("/v8/finance/chart/{$symbol}", [
            'range'    => $range,
            'interval' => $interval,
            'region'   => 'ID',
            'events'   => 'div,split',
        ]);

        $result     = $data['chart']['result'][0] ?? null;
        $timestamps = $result['timestamp'] ?? [];
        $quotes     = $result['indicators']['quote'][0] ?? [];
        $chartData  = [];

        foreach ($timestamps as $i => $ts) {
            $close = $quotes['close'][$i] ?? null;
            if ($close === null) continue;
            $chartData[] = [
                'time'   => $ts,
                'open'   => $quotes['open'][$i] ?? $close,
                'high'   => $quotes['high'][$i] ?? $close,
                'low'    => $quotes['low'][$i] ?? $close,
                'close'  => $close,
                'volume' => $quotes['volume'][$i] ?? null,
            ];
        }

        return $chartData;
    }

    private function apiGet(string $path, array $params = []): array
    {
        if ($this->apiKey === '') {
            throw new \RuntimeException('YFAPI_KEY belum dikonfigurasi.');
        }

        $response = Http::withHeaders([
            'accept'    => 'application/json',
            'X-API-KEY' => $this->apiKey,
        ])->timeout(15)->get($this->baseUrl . $path, $params);

        if ($response->status() === 429) {
            throw new \RuntimeException('YFApi.net rate-limit. Tunggu sebentar lalu coba lagi.');
        }

        if ($response->failed()) {
            throw new \RuntimeException('YFApi.net error: HTTP ' . $response->status());
        }

        return $response->json() ?? [];
    }

    private function fetchNews(string $symbol): array
    {
        try {
            $response = Http::withHeaders([
                'accept'     => 'application/json',
                'User-Agent' => config('idx.user_agent', 'Mozilla/5.0'),
            ])->timeout(10)->get(config('services.yahoo_finance.search_url'), [
                'q'           => $symbol,
                'quotesCount' => 0,
                'newsCount'   => 8,
                'region'      => 'ID',
                'lang'        => 'id-ID',
            ]);

            if ($response->failed()) {
                Log::warning('Yahoo Finance news request failed.', [
                    'symbol' => $symbol,
                    'status' => $response->status(),
                ]);

                return [];
            }

            return collect($response->json('news', []))
                ->map(fn (array $news) => [
                    'title'       => $news['title'] ?? null,
                    'source'      => $news['publisher'] ?? null,
                    'url'         => $this->externalUrl($news['link'] ?? null),
                    'publishedAt' => isset($news['providerPublishTime'])
                        ? Carbon::createFromTimestamp($news['providerPublishTime'])->toIso8601String()
                        : null,
                ])
                ->filter(fn (array $news) => filled($news['title']) && filled($news['url']))
                ->values()
                ->all();
        } catch (\Throwable $e) {
            Log::warning('Yahoo Finance news request failed.', [
                'symbol'  => $symbol,
                'message' => $e->getMessage(),
            ]);

            return [];
        }
    }

    private function financialStatements(array $result): array
    {
        $periods = [];

        $this->mergeStatementRows(
            $periods,
            $result['incomeStatementHistory']['incomeStatementHistory'] ?? [],
            [
                'totalRevenue',
                'grossProfit',
                'operatingIncome',
                'netIncome',
                'ebit',
            ]
        );
        $this->mergeStatementRows(
            $periods,
            $result['balanceSheetHistory']['balanceSheetStatements'] ?? [],
            [
                'totalAssets',
                'totalLiab',
                'totalStockholderEquity',
                'cash',
            ]
        );
        $this->mergeStatementRows(
            $periods,
            $result['cashflowStatementHistory']['cashflowStatements'] ?? [],
            [
                'totalCashFromOperatingActivities',
                'totalCashflowsFromInvestingActivities',
                'totalCashFromFinancingActivities',
            ]
        );

        krsort($periods);

        return array_values($periods);
    }

    private function mergeStatementRows(array &$periods, array $rows, array $fields): void
    {
        foreach ($rows as $row) {
            $endDate = $row['endDate']['fmt'] ?? null;
            if (!$endDate) {
                continue;
            }

            $periods[$endDate] ??= ['endDate' => $endDate];
            foreach ($fields as $field) {
                $periods[$endDate][$field] = $this->raw($row, $field);
            }
        }
    }

    private function raw(array $data, string $key): mixed
    {
        return $data[$key]['raw'] ?? null;
    }

    private function externalUrl(?string $url): ?string
    {
        if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        return in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https'], true) ? $url : null;
    }

    private function symbol(string $code): string
    {
        $code = strtoupper(trim($code));
        return str_ends_with($code, '.JK') ? $code : $code . '.JK';
    }

    private function intervalFor(string $range): string
    {
        return match ($range) {
            '1d'  => '1m',
            '5d'  => '5m',
            '1mo' => '1h',
            default => '1d',
        };
    }

    private function normalizeRange(string $range): string
    {
        return in_array($range, ['5d', '1mo', '3mo', '6mo', 'ytd', '1y', '2y', '5y'], true)
            ? $range : '1y';
    }
}
