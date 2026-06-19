<?php

namespace App\Jobs;

use App\Models\InvestmentManager;
use App\Models\ReksaDana;
use App\Models\SyncChangeLog;
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
            $run->markFailed('Backend sync URL tidak dikonfigurasi. Sync MI membutuhkan backend sync.');
            return;
        }

        try {
            // Step 1: Fetch & upsert MI
            $run->markStep('fetch_mi', 'Mengambil data MI dari backend...', 20);

            $miData = $backend->fetchMiData();

            $run->markStep('upsert_mi', 'Menyimpan ' . count($miData) . ' data MI ke database...', 40);

            $created = 0;
            $updated = 0;
            $skipped = 0;

            foreach ($miData as $item) {
                $nama = $item['name'] ?? '';
                if (empty($nama)) {
                    $skipped++;
                    continue;
                }

                $manager = InvestmentManager::where(function ($q) use ($item, $nama) {
                    if (!empty($item['kode_mi'])) {
                        $q->where('kode_mi', $item['kode_mi']);
                    } elseif (!empty($item['pasardana_id'])) {
                        $q->where('pasardana_id', $item['pasardana_id']);
                    } else {
                        $q->where('name', $nama);
                    }
                })->first();

                if ($manager) {
                    $oldAttrs = $manager->getRawOriginal();
                    $manager->update($item);
                    $updated++;

                    $oldModel = new InvestmentManager;
                    $oldModel->setRawAttributes($oldAttrs);
                    SyncChangeLog::captureModelDiff(
                        $run->id, 'mi', $oldModel, $item,
                        $nama . ($manager->kode_mi ? ' (' . $manager->kode_mi . ')' : ''), $manager->id
                    );
                } else {
                    $record = InvestmentManager::create($item);
                    $created++;

                    SyncChangeLog::logCreated(
                        $run->id, 'mi', $item,
                        $nama, $record->id
                    );
                }
            }

            // Step 2: Fetch RD & update relasi MI→RD
            $run->markStep('relasi', 'Mengupdate relasi MI → RD...', 70);

            $rdData = $backend->fetchRdData();
            $relasiMatched = 0;
            $relasiSkipped = 0;

            $miByPasardanaId = InvestmentManager::whereNotNull('pasardana_id')
                ->get(['id', 'pasardana_id'])
                ->keyBy('pasardana_id');

            foreach ($rdData as $rd) {
                $rdPasardanaId = $rd['pasardana_id'] ?? null;
                $rdNamaMi = $rd['nama_manajer_investasi'] ?? null;

                if (!$rdPasardanaId && !$rdNamaMi) {
                    $relasiSkipped++;
                    continue;
                }

                $localRd = ReksaDana::where(function ($q) use ($rd) {
                    if (!empty($rd['kode_reksa_dana'])) {
                        $q->where('kode_reksa_dana', $rd['kode_reksa_dana']);
                    } elseif (!empty($rd['pasardana_id'])) {
                        $q->where('pasardana_id', $rd['pasardana_id']);
                    }
                })->first();

                if (!$localRd) {
                    $relasiSkipped++;
                    continue;
                }

                $miId = null;
                $rdInvestmentManagerId = $rd['investment_manager_id'] ?? null;

                if ($rdInvestmentManagerId && isset($miByPasardanaId[$rdInvestmentManagerId])) {
                    $miId = $miByPasardanaId[$rdInvestmentManagerId]->id;
                }

                if (!$miId && $rdNamaMi) {
                    $miByName = InvestmentManager::where('name', $rdNamaMi)->first();
                    if ($miByName) {
                        $miId = $miByName->id;
                    }
                }

                if ($miId && $localRd->investment_manager_id !== $miId) {
                    $oldMiId = $localRd->investment_manager_id;
                    $localRd->update(['investment_manager_id' => $miId]);
                    $relasiMatched++;

                    SyncChangeLog::logUpdated($run->id, 'relasi_mi_rd', [
                        'investment_manager_id' => [
                            'old' => $oldMiId,
                            'new' => $miId,
                        ],
                    ], $localRd->nama_reksa_dana . ' → MI #' . $miId, $localRd->id);
                } else {
                    $relasiSkipped++;
                }
            }

            $summary = "Sync MI selesai. MI: {$created} baru, {$updated} update, {$skipped} skip. Relasi: {$relasiMatched} updated, {$relasiSkipped} skipped.";
            $run->markCompleted($summary, [
                'mi_created' => $created,
                'mi_updated' => $updated,
                'mi_skipped' => $skipped,
                'relasi_matched' => $relasiMatched,
                'relasi_skipped' => $relasiSkipped,
                'source' => 'pasardana_api_get',
            ]);

            $this->logActivity($run, 'Sync MI + Relasi dari Pasardana', $summary, 'success');
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
