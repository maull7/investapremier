<?php

namespace App\Jobs;

use App\Models\HargaReksaDana;
use App\Models\InvestmentManager;
use App\Models\InvestmentManagerPeriod;
use App\Models\ReksaDana;
use App\Models\SyncRun;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SyncInvestmentManagerPeriodsJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 600;
    public int $tries = 1;

    public function __construct(public int $syncRunId)
    {
        $this->onConnection('redis')->onQueue('extraction');
    }

    public function handle(): void
    {
        Log::info('SyncInvestmentManagerPeriodsJob started', ['sync_run_id' => $this->syncRunId]);

        $run = SyncRun::find($this->syncRunId);
        if (!$run) {
            Log::warning('SyncInvestmentManagerPeriodsJob: SyncRun not found', ['id' => $this->syncRunId]);
            return;
        }

        $managers = InvestmentManager::query()->orderBy('name')->get();
        $total = $managers->count();
        $completed = 0;
        $totalPeriods = 0;

        $run->markStep('process', 'Menghitung AUM periode dari data harian...', 5);

        foreach ($managers as $manager) {
            $completed++;
            $percent = 5 + (int) (($completed / $total) * 90);

            $run->update([
                'current_step_label' => "Memproses {$manager->name} ({$completed}/{$total})",
                'progress_percent' => $percent,
            ]);

            $fundIds = ReksaDana::query()
                ->where(function ($q) use ($manager) {
                    $q->where('nama_manajer_investasi', $manager->name)
                        ->orWhere('investment_manager_id', $manager->id);
                })
                ->pluck('id');

            if ($fundIds->isEmpty()) {
                continue;
            }

            $rows = HargaReksaDana::query()
                ->whereIn('reksa_dana_id', $fundIds)
                ->whereNotNull('aum')
                ->selectRaw('reksa_dana_id, tanggal, aum, unit_participation')
                ->orderBy('tanggal')
                ->get();

            if ($rows->isEmpty()) {
                continue;
            }

            $quarters = $rows
                ->groupBy(fn($r) => $r->tanggal->year . '-Q' . (int) ceil($r->tanggal->month / 3))
                ->map(function ($quarterRows) {
                    $latestPerFund = $quarterRows
                        ->groupBy('reksa_dana_id')
                        ->map(fn($fundRows) => $fundRows->sortByDesc('tanggal')->first());

                    return [
                        'aum' => $latestPerFund->sum(fn($r) => (float) $r->aum),
                        'up' => $latestPerFund->sum(fn($r) => (float) ($r->unit_participation ?? 0)),
                    ];
                });

            foreach ($quarters as $key => $agg) {
                preg_match('/^(\d+)-Q(\d)$/', $key, $m);
                $year = (int) $m[1];
                $quarter = (int) $m[2];
                $month = ($quarter - 1) * 3 + 3;
                $periodDate = "{$year}-{$month}-" . ($month == 12 ? '31' : '30');

                InvestmentManagerPeriod::updateOrCreate(
                    [
                        'investment_manager_id' => $manager->id,
                        'period_date' => $periodDate,
                    ],
                    [
                        'aum' => $agg['aum'] ?: null,
                        'up' => $agg['up'] ?: null,
                        'mata_uang' => 'IDR',
                        'tahun' => $year,
                        'kuartal' => $quarter,
                    ]
                );

                $totalPeriods++;
            }
        }

        $summary = "Sync periode MI selesai. {$total} MI diproses, {$totalPeriods} periode AUM dihitung dari data harian.";
        $run->markCompleted($summary, [
            'managers_processed' => $total,
            'periods_upserted' => $totalPeriods,
        ]);

        $this->logActivity($run, 'Sync Periode AUM MI', $summary, 'success');
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
            Log::warning('ActivityLog gagal saat job sync periode MI', ['error' => $e->getMessage()]);
        }
    }

    public function failed(\Throwable $e): void
    {
        $run = SyncRun::find($this->syncRunId);
        if ($run && !$run->isTerminal()) {
            $run->markFailed('Job gagal: ' . $e->getMessage(), [$e->getMessage()]);
        }
        Log::error('SyncInvestmentManagerPeriodsJob terminated', ['error' => $e->getMessage()]);
    }
}
