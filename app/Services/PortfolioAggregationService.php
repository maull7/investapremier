<?php

namespace App\Services;

use App\Models\MemberPortfolio;
use App\Models\PortofolioItem;
use App\Models\PerencanaanInvestasi;
use App\Models\ProgressCheckin;
use App\Models\QuizResult;
use App\Models\StockPriceAlert;
use App\Models\User;
use App\Models\AdvisorClientRequest;

class PortfolioAggregationService
{
    public function aggregate(User $user): array
    {
        $memberPortfolioTotal = MemberPortfolio::where('user_id', $user->id)->sum('total_nilai');
        $portofolioItemsTotal = PortofolioItem::where('user_id', $user->id)->sum('nilai');
        $totalKekayaan = $memberPortfolioTotal + $portofolioItemsTotal;

        $alokasi = $this->getAlokasiAset($user);
        $goals = $this->getGoals($user);
        $alerts = $this->getAlerts($user, $goals['items']);
        $quizResult = QuizResult::where('user_id', $user->id)->latest()->first();
        $advisor = $user->advisor;

        $likuiditas = collect($alokasi)->where('label', 'Kas/Deposito')->sum('nilai');
        $asetInvestasi = $totalKekayaan - $likuiditas;

        return [
            'totalKekayaan' => $totalKekayaan,
            'totalKekayaanFormatted' => $this->formatRupiahShort($totalKekayaan),
            'totalKekayaanGrowth' => 0,
            'asetInvestasi' => $asetInvestasi,
            'asetInvestasiFormatted' => $this->formatRupiahShort($asetInvestasi),
            'asetInvestasiPct' => $totalKekayaan > 0 ? round(($asetInvestasi / $totalKekayaan) * 100, 1) : 0,
            'likuiditas' => $likuiditas,
            'likuiditasFormatted' => $this->formatRupiahShort($likuiditas),
            'likuiditasPct' => $totalKekayaan > 0 ? round(($likuiditas / $totalKekayaan) * 100, 1) : 0,
            'nextReview' => $goals['nextReviewDate'],
            'nextReviewDays' => $goals['nextReviewDays'],
            'alokasiAset' => $alokasi,
            'goals' => $goals['items'],
            'alerts' => $alerts,
            'riskProfile' => $quizResult?->profile,
            'advisor' => $user->advisor ? [
                'name' => $user->advisor->name,
                'initial' => strtoupper(substr($user->advisor->name, 0, 2)),
            ] : null,
        ];
    }

    public function aggregateAdvisorClients(User $advisor): array
    {
        $clientIds = User::where('advisor_id', $advisor->id)->pluck('id');
        $totalAum = MemberPortfolio::whereIn('user_id', $clientIds)->sum('total_nilai')
            + PortofolioItem::whereIn('user_id', $clientIds)->sum('nilai');

        $clientAumList = User::whereIn('id', $clientIds)->get()->map(function ($client) {
            $mp = MemberPortfolio::where('user_id', $client->id)->sum('total_nilai');
            $pi = PortofolioItem::where('user_id', $client->id)->sum('nilai');
            $total = $mp + $pi;
            return [
                'id' => $client->id,
                'name' => $client->name,
                'email' => $client->email,
                'totalAum' => $total,
                'totalAumFormatted' => $this->formatRupiahShort($total),
                'riskProfile' => $client->memberProfile?->profil_risiko,
            ];
        })->sortByDesc('totalAum')->values();

        $pendingCount = AdvisorClientRequest::where('advisor_id', $advisor->id)
            ->where('status', 'pending')->count();

        $recentClients = $clientAumList->take(5);

        return [
            'totalClients' => $clientIds->count(),
            'totalAum' => $totalAum,
            'totalAumFormatted' => $this->formatRupiahShort($totalAum),
            'pendingCount' => $pendingCount,
            'averageAum' => $clientIds->count() > 0 ? $totalAum / $clientIds->count() : 0,
            'averageAumFormatted' => $this->formatRupiahShort($clientIds->count() > 0 ? $totalAum / $clientIds->count() : 0),
            'clientAumList' => $clientAumList,
            'recentClients' => $recentClients,
        ];
    }

    private function getAlokasiAset(User $user): array
    {
        $items = PortofolioItem::where('user_id', $user->id)
            ->selectRaw('jenis, SUM(nilai) as total')
            ->groupBy('jenis')
            ->pluck('total', 'jenis');

        $memberItems = MemberPortfolio::where('user_id', $user->id)
            ->selectRaw('jenis, SUM(total_nilai) as total')
            ->groupBy('jenis')
            ->pluck('total', 'jenis');

        $merged = collect();
        foreach ($items as $jenis => $total) {
            $merged->put($jenis, $merged->get($jenis, 0) + $total);
        }
        foreach ($memberItems as $jenis => $total) {
            $merged->put($jenis, $merged->get($jenis, 0) + $total);
        }

        $total = $merged->sum();
        if ($total <= 0) return [];

        $labelMap = [
            'Saham' => 'Saham', 'Obligasi' => 'Obligasi',
            'Reksa Dana' => 'Reksa Dana', 'Reksadana' => 'Reksa Dana',
            'Unit Link' => 'Unit Link',
            'Kas/Deposito' => 'Kas/Deposito', 'Kas' => 'Kas/Deposito', 'Deposito' => 'Kas/Deposito',
        ];

        $grouped = collect();
        foreach ($merged as $jenis => $nilai) {
            $label = $labelMap[$jenis] ?? $jenis;
            $grouped->put($label, $grouped->get($label, 0) + $nilai);
        }

        return $grouped->map(function ($nilai, $label) use ($total) {
            $warnaMap = [
                'Saham' => 'from-green-600 to-green-400',
                'Obligasi' => 'from-blue-600 to-blue-400',
                'Reksa Dana' => 'from-amber-500 to-yellow-400',
                'Unit Link' => 'from-purple-600 to-violet-400',
                'Kas/Deposito' => 'from-cyan-500 to-cyan-400',
            ];
            return [
                'label' => $label,
                'pct' => round(($nilai / $total) * 100),
                'nilai' => $nilai,
                'nilaiFormatted' => $this->formatRupiahShort($nilai),
                'warna' => $warnaMap[$label] ?? 'from-gray-500 to-gray-400',
            ];
        })->values()->toArray();
    }

    private function getGoals(User $user): array
    {
        $plans = PerencanaanInvestasi::where('user_id', $user->id)
            ->with('progressCheckins')->get();

        $items = $plans->map(function ($plan) {
            $latestCheckin = $plan->progressCheckins->sortByDesc('tanggal_checkin')->first();
            $terkumpul = $latestCheckin?->dana_terkumpul ?? 0;
            $target = $plan->kebutuhan_dana ?? 1;
            $pct = $target > 0 ? min(round(($terkumpul / $target) * 100), 100) : 0;

            return [
                'nama' => $plan->kategori_perencanaan,
                'pct' => $pct,
                'target' => $target,
                'targetFormatted' => $this->formatRupiahShort($target),
                'terkumpul' => $terkumpul,
                'terkumpulFormatted' => $this->formatRupiahShort($terkumpul),
            ];
        });

        $nextReviewDate = null;
        $nextReviewDays = null;
        $latestCheckin = ProgressCheckin::where('user_id', $user->id)
            ->latest('tanggal_checkin')->first();
        if ($latestCheckin) {
            $nextReviewDate = $latestCheckin->tanggal_checkin->addDays(90);
            $nextReviewDays = now()->diffInDays($nextReviewDate, false);
            $nextReviewDays = max(0, $nextReviewDays);
        }

        return [
            'items' => $items->toArray(),
            'nextReviewDate' => $nextReviewDate ? $nextReviewDate->format('d M Y') : null,
            'nextReviewDays' => $nextReviewDays,
        ];
    }

    private function getAlerts(User $user, array $goals): array
    {
        $alerts = [];

        foreach ($goals as $goal) {
            if ($goal['pct'] < 100 && $goal['target'] > 0) {
                $defisit = $goal['target'] - $goal['terkumpul'];
                if ($defisit > 0) {
                    $alerts[] = [
                        'type' => 'danger',
                        'bgColor' => 'red',
                        'borderColor' => 'red',
                        'textColor' => 'red',
                        'icon' => 'alert',
                        'message' => "Goal {$goal['nama']} kurang {$this->formatRupiahShort($defisit)}",
                    ];
                }
            }
        }

        $activeAlerts = StockPriceAlert::where('user_id', $user->id)
            ->where('is_active', true)->get();
        foreach ($activeAlerts as $alert) {
            $alerts[] = [
                'type' => 'warning',
                'bgColor' => 'amber',
                'borderColor' => 'amber',
                'textColor' => 'amber',
                'icon' => 'bell',
                'message' => "Alert harga: {$alert->kode_efek} di {$alert->harga_target}",
            ];
        }

        return $alerts ?? [];
    }

    private function formatRupiahShort(float $amount): string
    {
        if ($amount >= 1_000_000_000) {
            return 'Rp ' . number_format($amount / 1_000_000_000, 1) . 'M';
        }
        if ($amount >= 1_000_000) {
            return 'Rp ' . number_format($amount / 1_000_000, 1) . 'M';
        }
        if ($amount >= 1_000) {
            return 'Rp ' . number_format($amount / 1_000, 0) . 'Rb';
        }
        return 'Rp ' . number_format($amount, 0);
    }
}
