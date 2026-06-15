<?php

namespace App\Jobs;

use App\Models\SyncRun;
use App\Services\BackendSyncService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SyncAllPasardanaJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 600;
    public int $tries = 1;

    public function __construct(public int $syncRunId)
    {
        $this->onConnection('redis')->onQueue('extraction');
    }

    public function handle(BackendSyncService $backend): void
    {
        Log::info('SyncAllPasardanaJob started', ['sync_run_id' => $this->syncRunId]);

        $run = SyncRun::find($this->syncRunId);
        if (!$run) {
            Log::warning('SyncAllPasardanaJob: SyncRun not found', ['id' => $this->syncRunId]);
            return;
        }

        if (!$backend->isAvailable()) {
            $run->markFailed('Backend sync URL tidak dikonfigurasi.');
            return;
        }

        try {
            // Step 1: Sync MI
            $run->markStep('sync_mi', 'Sync Manajer Investasi...', 10);

            $miData = $backend->fetchMiData();

            $miCreated = 0;
            $miUpdated = 0;
            $miSkipped = 0;

            foreach ($miData as $item) {
                $nama = $item['name'] ?? '';
                if (empty($nama)) { $miSkipped++; continue; }

                $manager = \App\Models\InvestmentManager::where(function ($q) use ($item, $nama) {
                    if (!empty($item['kode_mi'])) $q->where('kode_mi', $item['kode_mi']);
                    elseif (!empty($item['pasardana_id'])) $q->where('pasardana_id', $item['pasardana_id']);
                    else $q->where('name', $nama);
                })->first();

                if ($manager) { $manager->update($item); $miUpdated++; }
                else { \App\Models\InvestmentManager::create($item); $miCreated++; }
            }

            // Step 2: Sync RD
            $run->markStep('sync_rd', 'Sync Reksa Dana...', 30);

            $rdData = $backend->fetchRdData();

            $rdCreated = 0;
            $rdUpdated = 0;
            $rdSkipped = 0;
            $backendIdToLocalId = [];

            foreach ($rdData as $item) {
                $nama = $item['nama_reksa_dana'] ?? $item['name'] ?? '';
                if (empty($nama)) { $rdSkipped++; continue; }

                $existing = \App\Models\ReksaDana::where(function ($q) use ($item, $nama) {
                    if (!empty($item['kode_reksa_dana'])) $q->where('kode_reksa_dana', $item['kode_reksa_dana']);
                    elseif (!empty($item['pasardana_id'])) $q->where('pasardana_id', $item['pasardana_id']);
                    else $q->where('nama_reksa_dana', $nama);
                })->first();

                $attrs = [];
                foreach (['nama_reksa_dana','kode_reksa_dana','jenis','jenis_reksa_dana','kategori','mata_uang','nama_manajer_investasi','nab_per_unit','tanggal_nab','aum','total_unit','return_1d','return_1m','return_1y','return_3y','return_5y','sharpe_ratio_1y','sharpe_ratio_3y','sharpe_ratio_5y','stdev_1y','stdev_3y','stdev_5y','beta_1y','beta_3y','beta_5y','max_drawdown_1y','max_drawdown_3y','max_drawdown_5y','pasardana_id'] as $f) {
                    if (isset($item[$f])) $attrs[$f] = $item[$f];
                }

                if ($existing) {
                    $existing->update($attrs);
                    $rdUpdated++;
                    if (!empty($item['backend_id'])) {
                        $backendIdToLocalId[$item['backend_id']] = $existing->id;
                    }
                } else {
                    $record = \App\Models\ReksaDana::create($attrs);
                    $rdCreated++;
                    if (!empty($item['backend_id'])) {
                        $backendIdToLocalId[$item['backend_id']] = $record->id;
                    }
                }
            }

            // Step 3: Relasi MI → RD
            $run->markStep('relasi', 'Mengupdate relasi MI → RD...', 55);

            $miByPasardanaId = \App\Models\InvestmentManager::whereNotNull('pasardana_id')
                ->get(['id', 'pasardana_id'])
                ->keyBy('pasardana_id');

            $relasiMatched = 0;
            $relasiSkipped = 0;

            foreach ($rdData as $rd) {
                $rdPasardanaId = $rd['pasardana_id'] ?? null;
                $rdInvestmentManagerId = $rd['investment_manager_id'] ?? null;
                $rdNamaMi = $rd['nama_manajer_investasi'] ?? null;

                if (!$rdPasardanaId && !$rdNamaMi) { $relasiSkipped++; continue; }

                $localRd = \App\Models\ReksaDana::where(function ($q) use ($rd) {
                    if (!empty($rd['kode_reksa_dana'])) $q->where('kode_reksa_dana', $rd['kode_reksa_dana']);
                    elseif (!empty($rd['pasardana_id'])) $q->where('pasardana_id', $rd['pasardana_id']);
                })->first();

                if (!$localRd) { $relasiSkipped++; continue; }

                $miId = null;
                if ($rdInvestmentManagerId && isset($miByPasardanaId[$rdInvestmentManagerId])) {
                    $miId = $miByPasardanaId[$rdInvestmentManagerId]->id;
                }
                if (!$miId && $rdNamaMi) {
                    $miByName = \App\Models\InvestmentManager::where('name', $rdNamaMi)->first();
                    if ($miByName) $miId = $miByName->id;
                }

                if ($miId && $localRd->investment_manager_id !== $miId) {
                    $localRd->update(['investment_manager_id' => $miId]);
                    $relasiMatched++;
                } else {
                    $relasiSkipped++;
                }
            }

            // Step 4: Sync harga harian
            $run->markStep('harian', 'Sync Harga Harian...', 75);

            $harianData = $backend->fetchHargaReksaDanaData();

            $harianCreated = 0;
            $harianUpdated = 0;
            $harianSkipped = 0;

            foreach ($harianData as $item) {
                $backendRdId = $item['reksa_dana_id'] ?? null;
                $tanggal = $item['tanggal'] ?? null;
                if (!$backendRdId || !$tanggal) { $harianSkipped++; continue; }

                $reksaDanaId = $backendIdToLocalId[$backendRdId] ?? null;
                if (!$reksaDanaId) { $harianSkipped++; continue; }

                // Normalize ISO datetime to MySQL date format (kolom tanggal bertipe date)
                $tanggal = date('Y-m-d', strtotime($tanggal));

                $attrs = ['reksa_dana_id' => $reksaDanaId, 'tanggal' => $tanggal];
                if (isset($item['nab_per_unit'])) $attrs['nab_per_unit'] = $item['nab_per_unit'];
                if (isset($item['aum'])) $attrs['aum'] = $item['aum'];
                if (isset($item['unit_participation'])) $attrs['unit_participation'] = $item['unit_participation'];

                $record = \App\Models\HargaReksaDana::updateOrCreate(
                    ['reksa_dana_id' => $reksaDanaId, 'tanggal' => $tanggal],
                    $attrs
                );

                if ($record->wasRecentlyCreated) { $harianCreated++; } else { $harianUpdated++; }
            }

            $summary = "Sync All Pasardana selesai. MI: {$miCreated}/{$miUpdated}/{$miSkipped}. RD: {$rdCreated}/{$rdUpdated}/{$rdSkipped}. Relasi: {$relasiMatched}/{$relasiSkipped}. Harian: {$harianCreated}/{$harianUpdated}/{$harianSkipped}.";
            $run->markCompleted($summary, [
                'mi' => ['created' => $miCreated, 'updated' => $miUpdated, 'skipped' => $miSkipped],
                'rd' => ['created' => $rdCreated, 'updated' => $rdUpdated, 'skipped' => $rdSkipped],
                'relasi' => ['matched' => $relasiMatched, 'skipped' => $relasiSkipped],
                'harian' => ['created' => $harianCreated, 'updated' => $harianUpdated, 'skipped' => $harianSkipped],
                'source' => 'pasardana_api_get',
            ]);

            $this->logActivity($run, 'Sync All Pasardana', $summary, 'success');
        } catch (\Throwable $e) {
            $msg = 'Gagal Sync All Pasardana: ' . $e->getMessage();
            $run->markFailed($msg);
            Log::error('SyncAllPasardanaJob error', ['error' => $e->getMessage()]);
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
            Log::warning('ActivityLog gagal saat job sync all', ['error' => $e->getMessage()]);
        }
    }

    public function failed(\Throwable $e): void
    {
        $run = SyncRun::find($this->syncRunId);
        if ($run && !$run->isTerminal()) {
            $run->markFailed('Job gagal: ' . $e->getMessage(), [$e->getMessage()]);
        }
        Log::error('SyncAllPasardanaJob terminated', ['error' => $e->getMessage()]);
    }
}
