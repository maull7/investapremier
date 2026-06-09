<?php

namespace App\Jobs;

use App\Models\SyncRun;
use App\Services\Extractors\IdxAiDataExtractorService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Log;

class SyncSahamFromIdxJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 300;
    public int $tries = 1;

    public function __construct(public int $syncRunId)
    {
        $this->onQueue('extraction');
    }

    public function middleware(): array
    {
        return [(new WithoutOverlapping('sync-saham-idx'))->expireAfter(400)];
    }

    public function handle(IdxAiDataExtractorService $extractor): void
    {
        $run = SyncRun::find($this->syncRunId);
        if (!$run) {
            Log::warning('SyncSahamFromIdxJob: SyncRun not found', ['id' => $this->syncRunId]);
            return;
        }

        // Pre-flight
        $run->markStep('preflight', 'Memeriksa lingkungan server (Node + Playwright + Chromium)', 2);
        $problems = $extractor->preflightCheck(true);
        if (!empty($problems)) {
            $msg = 'Lingkungan server belum siap. ' . implode(' | ', $problems);
            $run->markFailed($msg, $problems);
            Log::error('SyncSahamFromIdxJob preflight failed', ['problems' => $problems]);
            return;
        }

        $masterUrl = 'https://www.idx.co.id/id/data-pasar/data-saham/daftar-saham';
        $priceUrl = 'https://www.idx.co.id/id/data-pasar/ringkasan-perdagangan/ringkasan-saham';

        $run->markStep('extract', 'Mengambil master saham + ringkasan harga dari IDX', 20);

        try {
            $result = $extractor->extract($masterUrl, 'saham', null, $priceUrl, true);
        } catch (\Throwable $e) {
            $run->markFailed('Sync IDX gagal saat ekstraksi: ' . $e->getMessage(), [$e->getMessage()]);
            return;
        }

        if (!($result['success'] ?? false) || empty($result['data'])) {
            $msg = $result['message'] ?? 'Sync IDX gagal: tidak ada data yang dapat diekstrak.';
            $run->markFailed($msg);
            return;
        }

        $run->markStep('upsert', 'Menyimpan ke database (preserve sektor manual)', 80);
        $upsert = $extractor->upsertStocks($result['data'], true);
        $merge = $result['merge_stats'] ?? null;

        $matchInfo = '';
        if (is_array($merge) && !empty($merge['primary_count'])) {
            $rate = round((float) ($merge['match_rate'] ?? 0) * 100, 1);
            $matchInfo = " (harga matched: {$merge['filled_price_count']}/{$merge['primary_count']}, {$rate}%)";
        }

        $summary = "Sync saham selesai. Baru: {$upsert['created']}, Update: {$upsert['updated']}, Skip: {$upsert['skipped']}{$matchInfo}";

        $run->markCompleted($summary, [
            'upsert' => $upsert,
            'merge' => $merge,
        ]);

        try {
            if ($run->user_id) {
                \App\Models\ActivityLog::create([
                    'user_id' => $run->user_id,
                    'aksi' => 'Sync Saham dari IDX',
                    'keterangan' => $summary,
                    'status' => 'success',
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('ActivityLog gagal saat job sync saham', ['error' => $e->getMessage()]);
        }
    }

    public function failed(\Throwable $e): void
    {
        $run = SyncRun::find($this->syncRunId);
        if ($run && !$run->isTerminal()) {
            $run->markFailed('Job gagal: ' . $e->getMessage(), [$e->getMessage()]);
        }
        Log::error('SyncSahamFromIdxJob terminated', ['error' => $e->getMessage()]);
    }
}
