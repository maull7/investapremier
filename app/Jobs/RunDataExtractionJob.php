<?php

namespace App\Jobs;

use App\Models\ExtractedBondData;
use App\Models\ExtractedStockDailyTransaction;
use App\Models\ExtractionBatch;
use App\Models\ObligasiHargaReferensi;
use App\Models\Stock;
use App\Services\Extractors\BondDataExtractorService;
use App\Services\Extractors\StockDailyTransactionExtractorService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class RunDataExtractionJob implements ShouldQueue
{
    use Queueable;

    public int $tries;
    public int $timeout;

    public function __construct(public int $batchId)
    {
        $this->tries = (int) config('services.extraction.job_tries', 3);
        $this->timeout = (int) config('services.extraction.job_timeout', 120);
        $this->onConnection('redis');
        $this->onQueue(config('services.extraction.queue', 'extraction'));
    }

    public function handle(
        StockDailyTransactionExtractorService $stockExtractor,
        BondDataExtractorService $bondExtractor,
    ): void {
        $batch = ExtractionBatch::findOrFail($this->batchId);

        $batch->update([
            'status' => 'processing',
            'error_message' => null,
            'started_at' => now(),
            'finished_at' => null,
        ]);

        try {
            $parameters = [
                'data_type' => $batch->data_type,
                'data_date' => $batch->data_date?->toDateString(),
                'codes' => $this->selectedCodes($batch),
            ];

            $rows = match ($batch->data_type) {
                'stock_daily_transaction' => $this->extractStock($batch, $parameters, $stockExtractor),
                'bond_data' => $this->extractBond($batch, $parameters, $bondExtractor),
                default => throw new \InvalidArgumentException('Jenis data tidak didukung.'),
            };

            $this->storeRows($batch, $rows['rows'] ?? []);

            $batch->update([
                'status' => 'success',
                'total_records' => count($rows['rows'] ?? []),
                'error_message' => !empty($rows['errors'] ?? []) ? implode(' | ', array_slice($rows['errors'], 0, 5)) : null,
                'finished_at' => now(),
            ]);
        } catch (\Throwable $e) {
            $batch->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'finished_at' => now(),
            ]);

            Log::error('Data extraction failed.', [
                'batch_id' => $batch->id,
                'data_type' => $batch->data_type,
                'source' => $batch->source,
                'identifier' => $batch->identifier,
                'message' => $e->getMessage(),
                'exception' => $e::class,
            ]);
        }
    }

    private function extractStock(ExtractionBatch $batch, array $parameters, StockDailyTransactionExtractorService $extractor): array
    {
        return $extractor->extract($parameters);
    }

    private function extractBond(ExtractionBatch $batch, array $parameters, BondDataExtractorService $extractor): array
    {
        return $extractor->extract($parameters);
    }

    private function selectedCodes(ExtractionBatch $batch): array
    {
        $start = (int) ($batch->range_start ?? 1);
        $end = (int) ($batch->range_end ?? 0);
        if ($start < 1 || $end < $start) {
            return [];
        }

        $count = $end - $start + 1;

        $query = $batch->data_type === 'stock_daily_transaction'
            ? Stock::query()->orderBy('kode')->select('kode')
            : ObligasiHargaReferensi::query()->orderBy('kode')->select('kode');

        return $query
            ->skip($start - 1)
            ->take($count)
            ->pluck('kode')
            ->map(fn ($code) => strtoupper($code))
            ->all();
    }

    private function storeRows(ExtractionBatch $batch, array $rows): void
    {
        match ($batch->data_type) {
            'stock_daily_transaction' => $this->storeStockRows($batch, $rows),
            'bond_data' => $this->storeBondRows($batch, $rows),
            default => null,
        };
    }

    private function storeStockRows(ExtractionBatch $batch, array $rows): void
    {
        foreach ($rows as $row) {
            ExtractedStockDailyTransaction::updateOrCreate(
                [
                    'stock_code' => strtoupper($row['stock_code']),
                    'data_date' => $row['data_date'],
                    'source' => $row['source'],
                ],
                $row + ['extraction_batch_id' => $batch->id]
            );
        }
    }

    private function storeBondRows(ExtractionBatch $batch, array $rows): void
    {
        foreach ($rows as $row) {
            ExtractedBondData::updateOrCreate(
                [
                    'bond_code' => strtoupper($row['bond_code']),
                    'data_date' => $row['data_date'],
                    'source' => $row['source'],
                ],
                $row + ['extraction_batch_id' => $batch->id]
            );
        }
    }

}
