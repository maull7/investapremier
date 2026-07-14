<?php

namespace App\Http\Controllers;

use App\Models\ReksaDana;
use Illuminate\Http\Request;

class ReksaDanaController extends Controller
{
    private const JENIS_OPTIONS = ['Saham', 'Pendapatan Tetap', 'Campuran', 'Pasar Uang', 'Terproteksi', 'Global', 'DIRE-DINFRA', 'Penyertaan terbatas'];
    private const KATEGORI_OPTIONS = ['Konvensional', 'Syariah', 'index', 'ETF'];

    public function index(Request $request)
    {
        $query = ReksaDana::orderBy('nama_reksa_dana');

        if ($request->filled('jenis')) {
            $query->whereIn('jenis', (array) $request->jenis);
        }

        if ($request->filled('kategori')) {
            $kategoriFilter = (array) $request->kategori;
            $query->where(function ($q) use ($kategoriFilter) {
                foreach ($kategoriFilter as $k) {
                    $q->whereJsonContains('kategori', $k);
                }
            });
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('nama_reksa_dana', 'like', "%{$s}%")
                  ->orWhere('kode_reksa_dana', 'like', "%{$s}%");
            });
        }

        $reksaDanas = $query->paginate(20)->withQueryString();

        return view('reksa-dana.index', [
            'reksaDanas'      => $reksaDanas,
            'jenisOptions'    => self::JENIS_OPTIONS,
            'kategoriOptions' => self::KATEGORI_OPTIONS,
        ]);
    }

    public function show($id, \App\Services\ReksaDanaChartDataService $chartDataService)
    {
        $fund = ReksaDana::with([
            'harga' => fn($q) => $q->orderBy('tanggal'),
            'assetAllocations' => fn($q) => $q->orderBy('period_date'),
            'portfolioCompositions' => fn($q) => $q->orderBy('period_date'),
            'managementTeams',
            'investmentManager',
            'documents' => fn($q) => $q->with(['parsedPages', 'partitions']),
        ])->findOrFail($id);

        $range = request('range', '1y');
        $chartData = $chartDataService->forFund($fund, $range, request('from_date'), request('to_date'));

        $navHistoryQuery = $fund->harga()->orderBy('tanggal');
        if ($chartData['from']) $navHistoryQuery->whereDate('tanggal', '>=', $chartData['from']);
        if ($chartData['to']) $navHistoryQuery->whereDate('tanggal', '<=', $chartData['to']);

        $navHistory = $navHistoryQuery->get();
        $navLabels = $navHistory->pluck('tanggal')->map(fn($d) => $d->format('d M Y'));
        $navValues = $navHistory->pluck('nab_per_unit');
        $aumValues = $navHistory->pluck('aum');
        $upValues = $navHistory->pluck('unit_participation');

        $aaTimeline = $fund->assetAllocations()->orderBy('period_date')->get();
        $aaLabels = $aaTimeline->pluck('period_date')->map(fn($d) => $d->format('M Y'));

        $latestPeriodDate = $fund->portfolioCompositions()->max('period_date');
        $topHoldings = collect();
        if ($latestPeriodDate) {
            $topHoldings = $fund->portfolioCompositions()->where('period_date', $latestPeriodDate)->orderByDesc('weight_percent')->get();
        }

        $portfolioTimeline = $fund->portfolioCompositions()->selectRaw('reksa_dana_id, period_date, security_name, security_type, weight_percent')->orderBy('period_date')->get()->groupBy('period_date');

        $latestNav = $navHistory->last();
        $firstNav = $navHistory->first();
        $returnYearly = null;
        if ($latestNav && $firstNav && $firstNav->nab_per_unit > 0 && $navHistory->count() > 1) {
            $returnYearly = (($latestNav->nab_per_unit - $firstNav->nab_per_unit) / $firstNav->nab_per_unit) * 100;
        }

        $prevDayNav = $latestNav ? $fund->harga()->where('tanggal', '<', $latestNav->tanggal)->orderByDesc('tanggal')->first() : null;
        $returnDaily = null;
        if ($latestNav && $prevDayNav && $prevDayNav->nab_per_unit > 0) {
            $returnDaily = (($latestNav->nab_per_unit - $prevDayNav->nab_per_unit) / $prevDayNav->nab_per_unit) * 100;
        }

        $prevMonthNav = $fund->harga()->where('tanggal', '<=', now()->subMonth())->orderByDesc('tanggal')->first();
        $returnMonthly = null;
        if ($latestNav && $prevMonthNav && $prevMonthNav->nab_per_unit > 0) {
            $returnMonthly = (($latestNav->nab_per_unit - $prevMonthNav->nab_per_unit) / $prevMonthNav->nab_per_unit) * 100;
        }

        if ($returnDaily === null && $fund->return_1d !== null) $returnDaily = (float) $fund->return_1d * 100;
        if ($returnMonthly === null && $fund->return_1m !== null) $returnMonthly = (float) $fund->return_1m * 100;
        if ($returnYearly === null && $fund->return_1y !== null) $returnYearly = (float) $fund->return_1y * 100;

        $riskMetrics = [
            'sharpe_ratio_1y' => $fund->sharpe_ratio_1y, 'sharpe_ratio_3y' => $fund->sharpe_ratio_3y, 'sharpe_ratio_5y' => $fund->sharpe_ratio_5y,
            'stdev_1y' => $fund->stdev_1y, 'stdev_3y' => $fund->stdev_3y, 'stdev_5y' => $fund->stdev_5y,
            'beta_1y' => $fund->beta_1y, 'beta_3y' => $fund->beta_3y, 'beta_5y' => $fund->beta_5y,
            'max_drawdown_1y' => $fund->max_drawdown_1y, 'max_drawdown_3y' => $fund->max_drawdown_3y, 'max_drawdown_5y' => $fund->max_drawdown_5y,
        ];

        return view('reksa-dana.show', compact('fund', 'navHistory', 'navLabels', 'navValues', 'aumValues', 'upValues', 'aaTimeline', 'aaLabels', 'topHoldings', 'portfolioTimeline', 'latestNav', 'returnDaily', 'returnMonthly', 'returnYearly', 'range', 'chartData', 'riskMetrics'));
    }

    public function edit(ReksaDana $reksaDana)
    {
        abort(404);
    }

    public function update(Request $request, ReksaDana $reksaDana)
    {
        abort(404);
    }

    public function destroy(ReksaDana $reksaDana)
    {
        abort(404);
    }
}
