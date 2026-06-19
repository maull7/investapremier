<?php

namespace App\Jobs;

use App\Models\HargaReksaDana;
use App\Models\MutualFundAssetAllocation;
use App\Models\MutualFundManagementTeam;
use App\Models\MutualFundPortfolioComposition;
use App\Models\InvestmentPersonRole;
use App\Models\ReksaDana;
use App\Models\SyncRun;
use App\Services\KodeReksaDanaParser;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ReplaceRewriteReksaDanaJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 300;
    public int $tries = 1;

    public function __construct(public int $syncRunId)
    {
        $this->onConnection('redis')->onQueue('extraction');
    }

    public function handle(): void
    {
        Log::info('ReplaceRewriteReksaDanaJob started', ['sync_run_id' => $this->syncRunId]);

        $run = SyncRun::find($this->syncRunId);
        if (!$run) {
            Log::warning('ReplaceRewriteReksaDanaJob: SyncRun not found', ['id' => $this->syncRunId]);
            return;
        }

        $run->update(['started_at' => now()]);

        try {
            $stats = [
                'duplicate_groups' => 0,
                'duplicates_removed' => 0,
                'kategori_produk_filled' => 0,
                'kategori_produk_skipped' => 0,
            ];

            // ──────────────────────────────────────────────
            // Step 1: Find duplicate names
            // ──────────────────────────────────────────────
            $run->markStep('find_duplicates', 'Mencari data duplikat...', 2);

            $duplicateNames = ReksaDana::select('nama_reksa_dana')
                ->groupBy('nama_reksa_dana')
                ->havingRaw('COUNT(*) > 1')
                ->pluck('nama_reksa_dana');

            $totalDups = $duplicateNames->count();
            $stats['duplicate_groups'] = $totalDups;

            Log::info('ReplaceRewrite: found duplicate groups', ['count' => $totalDups]);

            // ──────────────────────────────────────────────
            // Step 2: Process each duplicate group
            // ──────────────────────────────────────────────
            if ($totalDups > 0) {
                $run->markStep('processing_duplicates', "Memproses {$totalDups} grup duplikat...", 5);

                $processed = 0;
                foreach ($duplicateNames as $nama) {
                    $records = ReksaDana::where('nama_reksa_dana', $nama)
                        ->orderByRaw('kode_reksa_dana IS NOT NULL DESC')
                        ->orderBy('updated_at', 'desc')
                        ->get();

                    $keep = $records->shift();

                    foreach ($records as $dup) {
                        $this->transferHargaReksaDana($dup->id, $keep->id);
                        $this->transferRelatedRecords($dup->id, $keep->id);

                        $dup->delete();
                        $stats['duplicates_removed']++;
                    }

                    $keep->fillFromKode();

                    $processed++;
                    $progress = 5 + ($processed / $totalDups) * 30;
                    $run->markStep('processing_duplicates', "Duplikat: {$nama} ({$processed}/{$totalDups})", (int) $progress);
                }
            }

            // ──────────────────────────────────────────────
            // Step 3: Fix Kategori Produk
            // ──────────────────────────────────────────────
            $run->markStep('fixing_kategori_produk', 'Memperbaiki Kategori Produk...', 35);

            $nullKp = ReksaDana::whereNull('kategori_produk')->get();
            $totalNull = $nullKp->count();
            $filled = 0;
            $skipped = 0;
            $parser = app(KodeReksaDanaParser::class);

            foreach ($nullKp as $i => $rd) {
                $updated = false;

                // Priority 1: Parse from kode_reksa_dana
                if (!empty($rd->kode_reksa_dana)) {
                    $attrs = $parser->databaseAttributes($rd->kode_reksa_dana);
                    if (!empty($attrs['kategori_produk'])) {
                        $rd->update(['kategori_produk' => $attrs['kategori_produk']]);
                        $filled++;
                        $updated = true;
                    }
                }

                // Priority 2: Extract from kategori JSON array
                if (!$updated && is_array($rd->kategori) && count($rd->kategori) > 0) {
                    $validKp = ['Konvensional', 'Syariah', 'Index', 'ETF'];
                    foreach ($rd->kategori as $kat) {
                        if (in_array($kat, $validKp, true)) {
                            $rd->update(['kategori_produk' => $kat]);
                            $filled++;
                            $updated = true;
                            break;
                        }
                    }
                }

                if (!$updated) {
                    $skipped++;
                }

                if ($i > 0 && $i % 10 === 0) {
                    $progress = 35 + ($i / $totalNull) * 60;
                    $run->markStep('fixing_kategori_produk', "Kategori Produk: {$filled} terisi dari {$i}/{$totalNull}", (int) $progress);
                }
            }

            $stats['kategori_produk_filled'] = $filled;
            $stats['kategori_produk_skipped'] = $skipped;

            // ──────────────────────────────────────────────
            // Complete
            // ──────────────────────────────────────────────
            $message = "Selesai! {$stats['duplicate_groups']} grup duplikat ditemukan, {$stats['duplicates_removed']} record dihapus. Kategori Produk: {$filled} diperbaiki, {$skipped} tidak bisa diisi (data tidak mencukupi).";
            $run->markCompleted($message, $stats);

            $this->logActivity($run, 'Bersihkan & Perbaiki Data', $message, 'success');

        } catch (\Throwable $e) {
            $msg = 'Gagal menjalankan Bersihkan & Perbaiki Data: ' . $e->getMessage();
            $run->markFailed($msg);
            Log::error('ReplaceRewriteReksaDanaJob error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        }
    }

    private function transferHargaReksaDana(int $fromId, int $toId): void
    {
        $hargas = HargaReksaDana::where('reksa_dana_id', $fromId)->get();

        foreach ($hargas as $harga) {
            HargaReksaDana::updateOrCreate(
                ['reksa_dana_id' => $toId, 'tanggal' => $harga->tanggal],
                [
                    'nab_per_unit' => $harga->nab_per_unit,
                    'aum' => $harga->aum,
                    'unit_participation' => $harga->unit_participation,
                ]
            );
        }
    }

    private function transferRelatedRecords(int $fromId, int $toId): void
    {
        $tables = [
            MutualFundAssetAllocation::class    => 'reksa_dana_id',
            MutualFundPortfolioComposition::class => 'reksa_dana_id',
            MutualFundManagementTeam::class     => 'reksa_dana_id',
            InvestmentPersonRole::class         => 'reksa_dana_id',
        ];

        foreach ($tables as $modelClass => $foreignKey) {
            $modelClass::where($foreignKey, $fromId)->update([$foreignKey => $toId]);
        }

        // ReksaDanaDocument: pindahkan reksa_dana_id
        \App\Models\ReksaDanaDocument::where('reksa_dana_id', $fromId)->update(['reksa_dana_id' => $toId]);

        // DataSourceLink: pindahkan reksa_dana_id
        \App\Models\DataSourceLink::where('reksa_dana_id', $fromId)->update(['reksa_dana_id' => $toId]);
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
            Log::warning('ActivityLog gagal saat job replace-rewrite', ['error' => $e->getMessage()]);
        }
    }
}
