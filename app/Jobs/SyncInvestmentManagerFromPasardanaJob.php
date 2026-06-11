<?php

namespace App\Jobs;

use App\Models\InvestmentManager;
use App\Models\SyncRun;
use App\Services\BackendSyncService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SyncInvestmentManagerFromPasardanaJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 300;
    public int $tries = 1;

    public function __construct(public int $syncRunId)
    {
        $this->onConnection('redis')->onQueue('extraction');
    }

    public function handle(BackendSyncService $backend): void
    {
        Log::info('SyncInvestmentManagerFromPasardanaJob started', ['sync_run_id' => $this->syncRunId]);

        $run = SyncRun::find($this->syncRunId);
        if (!$run) {
            Log::warning('SyncInvestmentManagerFromPasardanaJob: SyncRun not found', ['id' => $this->syncRunId]);
            return;
        }

        if (!$backend->isAvailable()) {
            $run->markFailed('Backend sync URL tidak dikonfigurasi. Sync MI dari Pasardana membutuhkan backend sync.');
            return;
        }

        $run->markStep('sync_mi', 'Meminta sync MI dari Pasardana API via backend...', 30);

        try {
            $result = $backend->syncMi();

            if (!($result['success'] ?? false)) {
                $msg = $result['message'] ?? 'Backend API sync MI gagal tanpa pesan.';
                $run->markFailed($msg);
                return;
            }

            $run->markStep('fetch', 'Mengambil data MI yang sudah di-sync dari backend...', 70);

            $data = $backend->fetchMiData();

            $run->markStep('upsert', 'Menyimpan ' . count($data) . ' data MI ke database...', 90);

            $created = 0;
            $updated = 0;
            $skipped = 0;

            foreach ($data as $item) {
                if (empty($item['name'])) {
                    $skipped++;
                    continue;
                }

                $manager = InvestmentManager::where('name', $item['name'])->first();

                if ($manager) {
                    $manager->update($item);
                    $updated++;
                } else {
                    InvestmentManager::create($item);
                    $created++;
                }
            }

            $summary = "Sync MI dari Pasardana selesai. Baru: {$created}, Update: {$updated}, Skip: {$skipped}";
            $run->markCompleted($summary, [
                'created' => $created,
                'updated' => $updated,
                'skipped' => $skipped,
                'source' => 'pasardana_api',
            ]);

            $this->logActivity($run, 'Sync MI dari Pasardana', $summary, 'success');
        } catch (\Throwable $e) {
            $msg = 'Gagal sync MI dari Pasardana: ' . $e->getMessage();
            $run->markFailed($msg);
            Log::error('SyncInvestmentManagerFromPasardanaJob error', ['error' => $e->getMessage()]);
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
            Log::warning('ActivityLog gagal saat job sync MI', ['error' => $e->getMessage()]);
        }
    }

    public function failed(\Throwable $e): void
    {
        $run = SyncRun::find($this->syncRunId);
        if ($run && !$run->isTerminal()) {
            $run->markFailed('Job gagal: ' . $e->getMessage(), [$e->getMessage()]);
        }
        Log::error('SyncInvestmentManagerFromPasardanaJob terminated', ['error' => $e->getMessage()]);
    }
}
