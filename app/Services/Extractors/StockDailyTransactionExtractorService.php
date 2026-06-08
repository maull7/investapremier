<?php

namespace App\Services\Extractors;

use App\Models\Stock;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class StockDailyTransactionExtractorService implements ExtractorInterface
{
    public function __construct(
        private readonly YahooFinanceStockExtractorService $yahooFinanceStockExtractor,
    ) {
    }

    public function extract(array $parameters): array
    {
        $date = Carbon::parse($parameters['data_date'] ?? now())->toDateString();
        $selectedCodes = array_values(array_filter(array_map('strtoupper', $parameters['codes'] ?? [])));
        $rows = [];
        $errors = [];

        $idxUrl = config('services.extraction.sources.idx.stock_url');
        if (filled($idxUrl)) {
            try {
                $idxRows = $this->fetchFromIdx($idxUrl, $date, $selectedCodes);
                if (!empty($idxRows)) {
                    return [
                        'rows' => $idxRows,
                        'source' => 'IDX Market',
                        'errors' => [],
                    ];
                }
            } catch (\Throwable $e) {
                Log::warning('StockDailyTransactionExtractorService IDX gagal, fallback ke Yahoo.', [
                    'date' => $date,
                    'message' => $e->getMessage(),
                ]);
                $errors[] = $e->getMessage();
            }
        }

        $stockQuery = Stock::query()->orderBy('kode')->select(['kode', 'nama', 'market_capital']);
        if (!empty($selectedCodes)) {
            $stockQuery->whereIn('kode', $selectedCodes);
        }

        foreach ($stockQuery->get() as $stock) {
            try {
                $stockRows = $this->yahooFinanceStockExtractor->extract([
                    'identifier' => $stock->kode,
                    'data_date' => $date,
                ]);

                foreach ($stockRows as $row) {
                    $rows[] = $row + [
                        'market_cap' => $row['market_cap'] ?? $stock->market_capital,
                    ];
                }
            } catch (\Throwable $e) {
                $errors[] = "{$stock->kode}: " . $e->getMessage();
                Log::warning('StockDailyTransactionExtractorService Yahoo gagal.', [
                    'stock_code' => $stock->kode,
                    'date' => $date,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        if (empty($rows)) {
            throw new \RuntimeException(
                'Gagal mengekstrak data saham dari IDX dan Yahoo Finance: ' . implode(' | ', array_slice($errors, 0, 5))
            );
        }

        return [
            'rows' => $rows,
            'source' => 'Yahoo Finance',
            'errors' => $errors,
        ];
    }

    private function fetchFromIdx(string $url, string $date, array $selectedCodes = []): array
    {
        $response = Http::acceptJson()
            ->timeout((int) config('services.extraction.timeout', 20))
            ->retry((int) config('services.extraction.retry', 3), (int) config('services.extraction.retry_sleep_ms', 500))
            ->withHeaders(['User-Agent' => config('idx.user_agent', 'Mozilla/5.0')])
            ->get($url, ['date' => $date]);

        if ($response->status() === 401 || $response->status() === 403) {
            throw new \RuntimeException('Akses IDX untuk data saham membutuhkan otorisasi atau diblokir.');
        }

        if ($response->failed()) {
            throw new \RuntimeException('IDX gagal diakses: HTTP ' . $response->status());
        }

        $json = $response->json();
        $items = $json['data'] ?? $json['rows'] ?? $json ?? [];

        return collect($items)->map(function (array $row) use ($date, $selectedCodes) {
            $code = strtoupper(trim((string) ($row['stock_code'] ?? $row['kode_saham'] ?? $row['kode'] ?? '')));
            if ($code === '') {
                return null;
            }

            if (!empty($selectedCodes) && !in_array($code, $selectedCodes, true)) {
                return null;
            }

            return [
                'stock_code' => $code,
                'data_date' => $row['data_date'] ?? $date,
                'open' => $row['open'] ?? $row['pembukaan'] ?? null,
                'high' => $row['high'] ?? $row['tertinggi'] ?? null,
                'low' => $row['low'] ?? $row['terendah'] ?? null,
                'close' => $row['close'] ?? $row['harga_terakhir'] ?? $row['last'] ?? null,
                'volume' => $row['volume'] ?? null,
                'price_change' => $row['price_change'] ?? $row['perubahan'] ?? null,
                'change_percent' => $row['change_percent'] ?? $row['persentase_perubahan'] ?? null,
                'market_cap' => $row['market_cap'] ?? $row['market_capital'] ?? null,
                'source' => 'IDX Market',
                'raw_payload' => $row,
            ];
        })->filter(fn ($row) => is_array($row) && filled($row['stock_code']) && $row['close'] !== null)->values()->all();
    }
}
