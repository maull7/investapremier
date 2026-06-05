<?php

namespace App\Services;

use App\Models\HargaReksaDana;
use App\Models\InvestmentManager;
use App\Models\ReksaDana;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ReksaDanaChartDataService
{
    public function resolveRange(?string $range, ?string $fromDate, ?string $toDate): array
    {
        $to = $toDate ? Carbon::parse($toDate)->endOfDay() : now()->endOfDay();
        $from = $fromDate ? Carbon::parse($fromDate)->startOfDay() : match ($range) {
            '1m' => $to->copy()->subMonth()->startOfDay(),
            '3m' => $to->copy()->subMonths(3)->startOfDay(),
            '6m' => $to->copy()->subMonths(6)->startOfDay(),
            'ytd' => $to->copy()->startOfYear(),
            '1y' => $to->copy()->subYear()->startOfDay(),
            '3y' => $to->copy()->subYears(3)->startOfDay(),
            '5y' => $to->copy()->subYears(5)->startOfDay(),
            'all' => null,
            default => $to->copy()->subYear()->startOfDay(),
        };

        if ($from && $from->greaterThan($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        return [$from, $to];
    }

    public function forFund(ReksaDana $fund, ?string $range, ?string $fromDate = null, ?string $toDate = null): array
    {
        [$from, $to] = $this->resolveRange($range, $fromDate, $toDate);

        $fundHistory = $this->historyQuery(collect([$fund->id]), $from, $to)
            ->where('reksa_dana_id', $fund->id)
            ->get();

        $managerFundIds = ($fund->nama_manajer_investasi || $fund->investment_manager_id)
            ? ReksaDana::query()
                ->where(function ($query) use ($fund) {
                    if ($fund->nama_manajer_investasi) {
                        $query->where('nama_manajer_investasi', $fund->nama_manajer_investasi);
                    }
                    if ($fund->investment_manager_id) {
                        $method = $fund->nama_manajer_investasi ? 'orWhere' : 'where';
                        $query->{$method}('investment_manager_id', $fund->investment_manager_id);
                    }
                })
                ->pluck('id')
            : collect([$fund->id]);

        $managerHistory = $this->historyQuery($managerFundIds, $from, $to)->get();

        $managerAum = $this->monthlyTotalSeries($managerHistory, 'aum');
        $managerUp = $this->monthlyTotalSeries($managerHistory, 'unit_participation');
        $fundAum = $this->monthlyFundSeries($fundHistory, 'aum');
        $fundUp = $this->monthlyFundSeries($fundHistory, 'unit_participation');
        $nav = $this->dailyFundSeries($fundHistory, 'nab_per_unit');

        return [
            'range' => $range ?: '1y',
            'from' => $from?->toDateString(),
            'to' => $to->toDateString(),
            'has_data' => $managerAum || $managerUp || $fundAum || $fundUp || $nav,
            'aum' => [
                'series' => [
                    ['name' => 'AUM Manajer Investasi', 'data' => $managerAum],
                    ['name' => 'AUM Reksa Dana', 'data' => $fundAum],
                ],
            ],
            'up' => [
                'series' => [
                    ['name' => 'Total UP Manajer Investasi', 'data' => $managerUp],
                    ['name' => 'Total UP Reksa Dana', 'data' => $fundUp],
                ],
            ],
            'nav' => [
                'series' => [
                    ['name' => 'NAB/UP Reksa Dana', 'data' => $nav],
                ],
            ],
        ];
    }

    public function forManager(InvestmentManager $manager, ?string $range, ?string $fromDate = null, ?string $toDate = null): array
    {
        [$from, $to] = $this->resolveRange($range, $fromDate, $toDate);

        $fundIds = ReksaDana::query()
            ->where(function ($query) use ($manager) {
                $query->where('nama_manajer_investasi', $manager->name)
                    ->orWhere('investment_manager_id', $manager->id);
            })
            ->pluck('id');

        $history = $this->historyQuery($fundIds, $from, $to)->get();
        $managerAum = $this->monthlyTotalSeries($history, 'aum');
        $managerUp = $this->monthlyTotalSeries($history, 'unit_participation');

        if (!$managerAum || !$managerUp) {
            $periods = $manager->periods()
                ->when($from, fn($q) => $q->whereDate('period_date', '>=', $from->toDateString()))
                ->whereDate('period_date', '<=', $to->toDateString())
                ->orderBy('period_date')
                ->get();

            $managerAum = $managerAum ?: $this->periodSeries($periods, 'aum');
            $managerUp = $managerUp ?: $this->periodSeries($periods, 'up');
        }

        return [
            'range' => $range ?: '1y',
            'from' => $from?->toDateString(),
            'to' => $to->toDateString(),
            'has_data' => $managerAum || $managerUp,
            'aum' => [
                'series' => [
                    ['name' => 'AUM Manajer Investasi', 'data' => $managerAum],
                ],
            ],
            'up' => [
                'series' => [
                    ['name' => 'Total UP Manajer Investasi', 'data' => $managerUp],
                ],
            ],
        ];
    }

    private function historyQuery(Collection $fundIds, ?Carbon $from, Carbon $to)
    {
        return HargaReksaDana::query()
            ->whereIn('reksa_dana_id', $fundIds->filter()->values())
            ->when($from, fn($q) => $q->whereDate('tanggal', '>=', $from->toDateString()))
            ->whereDate('tanggal', '<=', $to->toDateString())
            ->orderBy('tanggal');
    }

    private function monthlyTotalSeries(Collection $history, string $field): array
    {
        return $history
            ->filter(fn($row) => $row->{$field} !== null)
            ->groupBy(fn($row) => $row->tanggal->format('Y-m'))
            ->map(function (Collection $rows) use ($field) {
                $month = Carbon::parse($rows->first()->tanggal)->startOfMonth();
                $value = $rows
                    ->groupBy('reksa_dana_id')
                    ->map(fn(Collection $fundRows) => (float) $fundRows->sortBy('tanggal')->last()->{$field})
                    ->sum();

                return [$month->timestamp * 1000, $value];
            })
            ->values()
            ->all();
    }

    private function monthlyFundSeries(Collection $history, string $field): array
    {
        return $history
            ->filter(fn($row) => $row->{$field} !== null)
            ->groupBy(fn($row) => $row->tanggal->format('Y-m'))
            ->map(function (Collection $rows) use ($field) {
                $latest = $rows->sortBy('tanggal')->last();

                return [$latest->tanggal->copy()->startOfMonth()->timestamp * 1000, (float) $latest->{$field}];
            })
            ->values()
            ->all();
    }

    private function dailyFundSeries(Collection $history, string $field): array
    {
        return $history
            ->filter(fn($row) => $row->{$field} !== null)
            ->map(fn($row) => [$row->tanggal->timestamp * 1000, (float) $row->{$field}])
            ->values()
            ->all();
    }

    private function periodSeries(Collection $periods, string $field): array
    {
        return $periods
            ->filter(fn($row) => $row->{$field} !== null)
            ->map(fn($row) => [$row->period_date->copy()->startOfMonth()->timestamp * 1000, (float) $row->{$field}])
            ->values()
            ->all();
    }
}
