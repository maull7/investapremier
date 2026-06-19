<?php

namespace App\Jobs;

use App\Models\SyncRun;
use App\Services\BackendSyncService;
use App\Services\Extractors\IdxAiDataExtractorService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Log;

class SyncObligasiFromIdxJob implements ShouldQueue
{
    use Queueable;

    /**
     * Allow up to 10 minutes per attempt (IDX API ~15s + PHEI Govt ~30s + PHEI
     * Corp ~110s + safety buffer for slow VPS networks).
     */
    public int $timeout = 600;
    public int $tries = 1;

    public function __construct(public int $syncRunId)
    {
        $this->onConnection('redis')->onQueue('extraction');
    }

    public function middleware(): array
    {
        // Prevent concurrent obligasi syncs (each one launches headless Chromium).
        return [(new WithoutOverlapping('sync-obligasi-idx-phei'))->expireAfter(700)];
    }

    public function handle(IdxAiDataExtractorService $extractor, BackendSyncService $backend): void
    {
        $run = SyncRun::find($this->syncRunId);
        if (!$run) {
            Log::warning('SyncObligasiFromIdxJob: SyncRun not found', ['id' => $this->syncRunId]);
            return;
        }

        if ($backend->isAvailable()) {
            $this->handleViaBackend($run, $backend);
            return;
        }

        $errors = [];

        // Pre-flight: catch missing Node/Playwright/Chromium early
        $run->markStep('preflight', 'Memeriksa lingkungan server (Node + Playwright + Chromium)', 2);
        $problems = $extractor->preflightCheck(true);
        if (!empty($problems)) {
            $msg = 'Lingkungan server belum siap. ' . implode(' | ', $problems);
            $run->markFailed($msg, $problems);
            Log::error('SyncObligasiFromIdxJob preflight failed', ['problems' => $problems]);
            return;
        }

        // 1. IDX Korporasi via internal API (~15s, ~1419 bonds, rating + emiten)
        $run->markStep('fetch_idx', 'Mengambil ~1419 obligasi korporasi + rating dari IDX', 10);
        $idxBonds = [];
        try {
            $idxBonds = $extractor->fetchIdxCorporateBonds();
        } catch (\Throwable $e) {
            $errors[] = 'IDX fetch gagal: ' . $e->getMessage();
            Log::warning('SyncObligasiFromIdxJob: IDX fetch failed', ['error' => $e->getMessage()]);
        }

        // 2. PHEI Pemerintah (~30s, ~296 bonds)
        $run->markStep('fetch_phei_govt', 'Mengambil obligasi pemerintah dari PHEI', 30);
        $pheiGovt = [];
        try {
            $pheiGovt = $extractor->fetchPheiBonds('pemerintah');
        } catch (\Throwable $e) {
            $errors[] = 'PHEI Pemerintah fetch gagal: ' . $e->getMessage();
            Log::warning('SyncObligasiFromIdxJob: PHEI Govt failed', ['error' => $e->getMessage()]);
        }

        // 3. PHEI Korporasi (~110s, ~1238 bonds)
        $run->markStep('fetch_phei_corp', 'Mengambil obligasi korporasi dari PHEI (~2 menit)', 55);
        $pheiCorp = [];
        try {
            $pheiCorp = $extractor->fetchPheiBonds('korporasi');
        } catch (\Throwable $e) {
            $errors[] = 'PHEI Korporasi fetch gagal: ' . $e->getMessage();
            Log::warning('SyncObligasiFromIdxJob: PHEI Corp failed', ['error' => $e->getMessage()]);
        }

        if (empty($idxBonds) && empty($pheiGovt) && empty($pheiCorp)) {
            $msg = 'Sync gagal: tidak ada data berhasil ditarik dari IDX maupun PHEI. ' . implode(' | ', $errors);
            $run->markFailed($msg, $errors);
            return;
        }

        // 4. Merge
        $run->markStep('merge', 'Menggabungkan data dari 3 sumber', 90);
        [$merged, $mergeStats] = $extractor->mergeBondResults($idxBonds, $pheiGovt, $pheiCorp);

        // 5. Upsert
        $run->markStep('upsert', 'Menyimpan ke database (preserve harga manual)', 95);
        $upsertStats = $extractor->upsertBonds($merged, true, $run->id);

        $summary = sprintf(
            'Sync obligasi selesai. Total %d obligasi (IDX: %d, PHEI Pemerintah: %d, PHEI Korporasi: %d). DB: %d baru, %d diupdate, %d dilewati. Harga & YTM tetap NULL — perlu diisi manual.',
            $mergeStats['merged_count'] ?? 0,
            $mergeStats['idx_count'] ?? 0,
            $mergeStats['phei_govt_count'] ?? 0,
            $mergeStats['phei_corp_count'] ?? 0,
            $upsertStats['created'] ?? 0,
            $upsertStats['updated'] ?? 0,
            $upsertStats['skipped'] ?? 0,
        );

        $run->markCompleted($summary, [
            'merge' => $mergeStats,
            'upsert' => $upsertStats,
        ], $errors);

        $this->logActivity($run, 'Sync Obligasi dari IDX+PHEI', $summary, empty($errors) ? 'success' : 'warning');
    }

    private function handleViaBackend(SyncRun $run, BackendSyncService $backend): void
    {
        $run->markStep('fetch', 'Mengambil data obligasi dari backend API...', 30);

        try {
            $data = $backend->fetchObligasiData();

            if (empty($data)) {
                $run->markFailed('Backend API: data obligasi kosong. Backend mungkin belum menjalankan sync pertama.');
                return;
            }

            $run->markStep('upsert', 'Menyimpan ' . count($data) . ' data obligasi ke database...', 70);

            $extractor = app(IdxAiDataExtractorService::class);
            $upsert = $extractor->upsertBonds($data, true, $run->id);

            $summary = "Sync obligasi via backend API selesai. Baru: {$upsert['created']}, Update: {$upsert['updated']}, Skip: {$upsert['skipped']}";

            $run->markCompleted($summary, [
                'upsert' => $upsert,
                'source' => 'backend_api',
            ]);

            $this->logActivity($run, 'Sync Obligasi dari Backend API', $summary, 'success');
        } catch (\Throwable $e) {
            $msg = 'Backend API tidak merespon. Pastikan backend sync sudah berjalan. (' . $e->getMessage() . ')';
            $run->markFailed($msg);
            Log::error('SyncObligasiFromIdxJob backend API error', ['error' => $e->getMessage()]);
        }
    }

    private function logActivity(SyncRun $run, string $aksi, string $keterangan, string $status): void
    {
        try {
            if ($run->user_id) {
                \App\Models\ActivityLog::create([
                    'user_id' => $run->user_id,
                    'aksi' => $aksi,
                    'keterangan' => $keterangan,
                    'status' => $status,
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('ActivityLog gagal saat job sync obligasi', ['error' => $e->getMessage()]);
        }
    }

    public function failed(\Throwable $e): void
    {
        $run = SyncRun::find($this->syncRunId);
        if ($run && !$run->isTerminal()) {
            $run->markFailed('Job gagal: ' . $e->getMessage(), [$e->getMessage()]);
        }
        Log::error('SyncObligasiFromIdxJob terminated', ['error' => $e->getMessage()]);
    }
}
