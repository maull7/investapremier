<?php

namespace App\Jobs;

use App\Models\HargaReksaDana;
use App\Models\ReksaDana;
use App\Models\SyncChangeLog;
use App\Models\SyncRun;
use App\Services\BackendSyncService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SyncReksaDanaFromPasardanaJob implements ShouldQueue
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
        Log::info('SyncReksaDanaFromPasardanaJob started', ['sync_run_id' => $this->syncRunId]);

        $run = SyncRun::find($this->syncRunId);
        if (!$run) {
            Log::warning('SyncReksaDanaFromPasardanaJob: SyncRun not found', ['id' => $this->syncRunId]);
            return;
        }

        if (!$backend->isAvailable()) {
            $run->markFailed('Backend sync URL tidak dikonfigurasi. Sync RD membutuhkan backend sync.');
            return;
        }

        try {
            $created = 0;
            $updated = 0;
            $skipped = 0;
            $harianCreated = 0;
            $harianUpdated = 0;
            $harianSkipped = 0;
            $backendIdToLocalId = [];

            // Step 1: Fetch & upsert RD
            $run->markStep('fetch_rd', 'Mengambil data RD dari backend...', 20);

            $rdData = $backend->fetchRdData();

            $run->markStep('upsert_rd', 'Menyimpan ' . count($rdData) . ' data RD ke database...', 40);

            foreach ($rdData as $item) {
                $nama = $item['nama_reksa_dana'] ?? $item['name'] ?? '';
                if (empty($nama)) {
                    $skipped++;
                    continue;
                }

                $existing = ReksaDana::where(function ($q) use ($item, $nama) {
                    if (!empty($item['kode_reksa_dana'])) {
                        $q->where('kode_reksa_dana', $item['kode_reksa_dana']);
                    } elseif (!empty($item['pasardana_id'])) {
                        $q->where('pasardana_id', $item['pasardana_id']);
                    } else {
                        $q->where('nama_reksa_dana', $nama);
                    }
                })->first();

                $attrs = [];
                if (isset($item['nama_reksa_dana'])) $attrs['nama_reksa_dana'] = $item['nama_reksa_dana'];
                if (isset($item['kode_reksa_dana'])) $attrs['kode_reksa_dana'] = $item['kode_reksa_dana'];
                if (isset($item['jenis'])) $attrs['jenis'] = $item['jenis'];
                if (isset($item['jenis_reksa_dana'])) $attrs['jenis_reksa_dana'] = $item['jenis_reksa_dana'];
                if (isset($item['kategori'])) $attrs['kategori'] = $item['kategori'];
                if (isset($item['mata_uang'])) $attrs['mata_uang'] = $item['mata_uang'];
                if (isset($item['nama_manajer_investasi'])) $attrs['nama_manajer_investasi'] = $item['nama_manajer_investasi'];
                if (isset($item['nab_per_unit'])) $attrs['nab_per_unit'] = $item['nab_per_unit'];
                if (isset($item['tanggal_nab'])) $attrs['tanggal_nab'] = $item['tanggal_nab'];
                if (isset($item['total_aum'])) $attrs['aum'] = $item['total_aum'];
                if (isset($item['aum'])) $attrs['aum'] = $item['aum'];
                if (isset($item['unit_penyertaan'])) $attrs['total_unit'] = $item['unit_penyertaan'];
                if (isset($item['total_unit'])) $attrs['total_unit'] = $item['total_unit'];
                if (isset($item['return_1d'])) $attrs['return_1d'] = $item['return_1d'];
                if (isset($item['return_1m'])) $attrs['return_1m'] = $item['return_1m'];
                if (isset($item['return_1y'])) $attrs['return_1y'] = $item['return_1y'];
                if (isset($item['return_3y'])) $attrs['return_3y'] = $item['return_3y'];
                if (isset($item['return_5y'])) $attrs['return_5y'] = $item['return_5y'];
                if (isset($item['sharpe_ratio_1y'])) $attrs['sharpe_ratio_1y'] = $item['sharpe_ratio_1y'];
                if (isset($item['sharpe_ratio_3y'])) $attrs['sharpe_ratio_3y'] = $item['sharpe_ratio_3y'];
                if (isset($item['sharpe_ratio_5y'])) $attrs['sharpe_ratio_5y'] = $item['sharpe_ratio_5y'];
                if (isset($item['stdev_1y'])) $attrs['stdev_1y'] = $item['stdev_1y'];
                if (isset($item['stdev_3y'])) $attrs['stdev_3y'] = $item['stdev_3y'];
                if (isset($item['stdev_5y'])) $attrs['stdev_5y'] = $item['stdev_5y'];
                if (isset($item['beta_1y'])) $attrs['beta_1y'] = $item['beta_1y'];
                if (isset($item['beta_3y'])) $attrs['beta_3y'] = $item['beta_3y'];
                if (isset($item['beta_5y'])) $attrs['beta_5y'] = $item['beta_5y'];
                if (isset($item['max_drawdown_1y'])) $attrs['max_drawdown_1y'] = $item['max_drawdown_1y'];
                if (isset($item['max_drawdown_3y'])) $attrs['max_drawdown_3y'] = $item['max_drawdown_3y'];
                if (isset($item['max_drawdown_5y'])) $attrs['max_drawdown_5y'] = $item['max_drawdown_5y'];
                if (isset($item['pasardana_id'])) $attrs['pasardana_id'] = $item['pasardana_id'];

                if ($existing) {
                    $oldAttrs = $existing->getRawOriginal();
                    $existing->update($attrs);
                    $updated++;
                    if (!empty($item['backend_id'])) {
                        $backendIdToLocalId[$item['backend_id']] = $existing->id;
                    }

                    $oldModel = new ReksaDana;
                    $oldModel->setRawAttributes($oldAttrs);
                    SyncChangeLog::captureModelDiff(
                        $run->id, 'rd', $oldModel, $attrs,
                        $nama, $existing->id
                    );
                } else {
                    $record = ReksaDana::create($attrs);
                    $created++;
                    if (!empty($item['backend_id'])) {
                        $backendIdToLocalId[$item['backend_id']] = $record->id;
                    }

                    SyncChangeLog::logCreated(
                        $run->id, 'rd', $attrs,
                        $nama, $record->id
                    );
                }
            }

            // Step 2: Fetch & upsert harga harian
            $run->markStep('fetch_harian', 'Mengambil data harga harian dari backend...', 60);

            $harianData = $backend->fetchHargaReksaDanaData();

            $run->markStep('upsert_harian', 'Menyimpan ' . count($harianData) . ' data harga harian ke database...', 85);

            foreach ($harianData as $item) {
                $backendRdId = $item['reksa_dana_id'] ?? null;
                $tanggal = $item['tanggal'] ?? null;
                if (!$backendRdId || !$tanggal) {
                    $harianSkipped++;
                    continue;
                }

                $reksaDanaId = $backendIdToLocalId[$backendRdId] ?? null;
                if (!$reksaDanaId) {
                    $harianSkipped++;
                    continue;
                }

                $tanggal = date('Y-m-d', strtotime($tanggal));

                $attrs = [
                    'reksa_dana_id' => $reksaDanaId,
                    'tanggal' => $tanggal,
                ];
                if (isset($item['nab_per_unit'])) $attrs['nab_per_unit'] = $item['nab_per_unit'];
                if (isset($item['aum'])) $attrs['aum'] = $item['aum'];
                if (isset($item['unit_participation'])) $attrs['unit_participation'] = $item['unit_participation'];

                $existing = HargaReksaDana::where('reksa_dana_id', $reksaDanaId)
                    ->where('tanggal', $tanggal)
                    ->first();

                if ($existing) {
                    $oldAttrs = $existing->getRawOriginal();
                    $existing->update($attrs);
                    $harianUpdated++;

                    $rdLabel = ReksaDana::find($reksaDanaId)?->nama_reksa_dana ?? 'RD#' . $reksaDanaId;
                    $diffs = [];
                    foreach ($attrs as $field => $newVal) {
                        if (in_array($field, ['reksa_dana_id', 'tanggal'])) continue;
                        $oldVal = $oldAttrs[$field] ?? null;
                        if ($oldVal instanceof \DateTime) $oldVal = $oldVal->format('Y-m-d');
                        if ((string) $oldVal !== (string) $newVal) {
                            $diffs[$field] = ['old' => $oldVal, 'new' => $newVal];
                        }
                    }
                    if ($diffs) {
                        SyncChangeLog::logUpdated(
                            $run->id, 'rd_harian', $diffs,
                            $rdLabel . ' - ' . $tanggal, $existing->id
                        );
                    }
                } else {
                    $record = HargaReksaDana::create($attrs);
                    $harianCreated++;

                    $rdLabel = ReksaDana::find($reksaDanaId)?->nama_reksa_dana ?? 'RD#' . $reksaDanaId;
                    SyncChangeLog::logCreated(
                        $run->id, 'rd_harian', array_diff_key($attrs, ['reksa_dana_id' => true, 'tanggal' => true]),
                        $rdLabel . ' - ' . $tanggal, $record->id
                    );
                }
            }

            $summary = "Sync RD selesai. RD: {$created} baru, {$updated} update, {$skipped} skip. Harga Harian: {$harianCreated} baru, {$harianUpdated} update, {$harianSkipped} skip.";
            $run->markCompleted($summary, [
                'rd_created' => $created,
                'rd_updated' => $updated,
                'rd_skipped' => $skipped,
                'harian_created' => $harianCreated,
                'harian_updated' => $harianUpdated,
                'harian_skipped' => $harianSkipped,
                'source' => 'pasardana_api_get',
            ]);

            $this->logActivity($run, 'Sync RD + Harga Harian dari Pasardana', $summary, 'success');
        } catch (\Throwable $e) {
            $msg = 'Gagal sync RD dari Pasardana: ' . $e->getMessage();
            $run->markFailed($msg);
            Log::error('SyncReksaDanaFromPasardanaJob error', ['error' => $e->getMessage()]);
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
            Log::warning('ActivityLog gagal saat job sync RD', ['error' => $e->getMessage()]);
        }
    }

    public function failed(\Throwable $e): void
    {
        $run = SyncRun::find($this->syncRunId);
        if ($run && !$run->isTerminal()) {
            $run->markFailed('Job gagal: ' . $e->getMessage(), [$e->getMessage()]);
        }
        Log::error('SyncReksaDanaFromPasardanaJob terminated', ['error' => $e->getMessage()]);
    }
}
