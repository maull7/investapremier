<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AnalisaController;
use App\Models\Stock;
use App\Models\ObligasiBond;
use App\Services\YahooStockDataService;
use Illuminate\Http\Request;

class AnalisaRdController extends AnalisaController
{
    protected bool $isAdminContext = true;

    protected function indexRoute(): string
    {
        return 'admin.reksa-dana.index';
    }

    public function lookupKodeEfek(Request $request)
    {
        $kode = strtoupper(trim($request->input('kode', '')));
        
        if (empty($kode)) {
            return response()->json(['found' => false, 'message' => 'Kode efek kosong']);
        }

        $stock = Stock::where('kode', $kode)->first();
        if ($stock) {
            return response()->json([
                'found' => true,
                'type' => 'saham',
                'data' => [
                    'kode_efek' => $stock->kode,
                    'nama_efek' => $stock->nama,
                    'sektor' => $stock->sektor,
                ]
            ]);
        }

        $bond = ObligasiBond::where('kode', $kode)->first();
        if ($bond) {
            return response()->json([
                'found' => true,
                'type' => 'obligasi',
                'data' => [
                    'kode_efek' => $bond->kode,
                    'nama_efek' => $kode,
                ]
            ]);
        }

        return response()->json(['found' => false, 'message' => 'Kode efek tidak ditemukan']);
    }

    public function getFinancialData(Request $request, YahooStockDataService $yahooService)
    {
        $kode = strtoupper(trim($request->input('kode', '')));
        $type = $request->input('type', 'saham');
        $useYahoo = $request->boolean('use_yahoo', true);
        
        if (empty($kode)) {
            return response()->json(['found' => false, 'message' => 'Kode efek kosong']);
        }

        if ($type === 'saham') {
            $stock = Stock::where('kode', $kode)->first();
            if ($stock) {
                $latestReport = $stock->financialReports()->latest('report_year')->first();
                
                $per = null;
                $pbv = null;
                $roe = null;
                $roa = null;
                $npm = null;
                $der = null;
                $current_ratio = null;
                $gross_profit_margin = null;
                $operating_profit_margin = null;
                $dataSource = 'database';
                
                if ($latestReport) {
                    // ROE = Net Income / Total Equity * 100
                    if ($latestReport->total_equity > 0) {
                        $roe = ($latestReport->net_income / $latestReport->total_equity) * 100;
                    }
                    
                    // ROA = Net Income / Total Asset * 100
                    if ($latestReport->total_asset > 0) {
                        $roa = ($latestReport->net_income / $latestReport->total_asset) * 100;
                    }
                    
                    // NPM = Net Income / Revenue * 100
                    if ($latestReport->revenue > 0) {
                        $npm = ($latestReport->net_income / $latestReport->revenue) * 100;
                    }
                    
                    // DER = Total Liabilities / Total Equity
                    if ($latestReport->total_equity > 0) {
                        $der = $latestReport->total_liabilities / $latestReport->total_equity;
                    }
                    
                    // Gross Profit Margin = (Revenue - COGS) / Revenue * 100
                    // Operating Profit Margin = Operating Income / Revenue * 100
                    if ($latestReport->revenue > 0 && $latestReport->operating_income) {
                        $operating_profit_margin = ($latestReport->operating_income / $latestReport->revenue) * 100;
                    }
                    
                    // PBV = Market Cap / Equity
                    if ($stock->market_capital && $latestReport->total_equity > 0) {
                        $pbv = $stock->market_capital / $latestReport->total_equity;
                    }
                    
                    // PER = Market Cap / Net Income (trailing)
                    if ($stock->market_capital && $latestReport->net_income > 0) {
                        $per = $stock->market_capital / $latestReport->net_income;
                    }
                }
                
                // ponytail: Fallback ke Yahoo Finance jika data lokal tidak ada atau tidak lengkap
                if ($useYahoo && (!$latestReport || !$per || !$pbv || !$roe)) {
                    try {
                        $yahooData = $yahooService->fetchSummary($stock);
                        $stats = $yahooData['stats'] ?? [];
                        $financials = $yahooData['financials'] ?? [];
                        
                        // Ambil data dari Yahoo Finance jika tidak ada di database
                        $per = $per ?? $stats['trailingPE'];
                        $pbv = $pbv ?? $stats['priceToBook'];
                        $npm = $npm ?? ($stats['profitMargins'] ? $stats['profitMargins'] * 100 : null);
                        
                        // Hitung ROE & ROA dari Yahoo financial statements
                        if (!empty($financials) && count($financials) > 0) {
                            $latest = $financials[0];
                            if (!$roe && isset($latest['netIncome'], $latest['totalStockholderEquity']) && $latest['totalStockholderEquity'] > 0) {
                                $roe = ($latest['netIncome'] / $latest['totalStockholderEquity']) * 100;
                            }
                            if (!$roa && isset($latest['netIncome'], $latest['totalAssets']) && $latest['totalAssets'] > 0) {
                                $roa = ($latest['netIncome'] / $latest['totalAssets']) * 100;
                            }
                            if (!$der && isset($latest['totalLiab'], $latest['totalStockholderEquity']) && $latest['totalStockholderEquity'] > 0) {
                                $der = $latest['totalLiab'] / $latest['totalStockholderEquity'];
                            }
                            if (!$gross_profit_margin && isset($latest['grossProfit'], $latest['totalRevenue']) && $latest['totalRevenue'] > 0) {
                                $gross_profit_margin = ($latest['grossProfit'] / $latest['totalRevenue']) * 100;
                            }
                            if (!$operating_profit_margin && isset($latest['operatingIncome'], $latest['totalRevenue']) && $latest['totalRevenue'] > 0) {
                                $operating_profit_margin = ($latest['operatingIncome'] / $latest['totalRevenue']) * 100;
                            }
                        }
                        
                        $dataSource = 'yahoo_finance';
                    } catch (\Exception $e) {
                        // Ignore Yahoo Finance errors, gunakan data lokal saja
                        \Log::info('Yahoo Finance fetch failed for ' . $kode . ': ' . $e->getMessage());
                    }
                }
                
                $hasData = $per || $pbv || $roe || $roa || $npm;
                
                return response()->json([
                    'found' => true,
                    'has_financial_data' => $hasData,
                    'data_source' => $dataSource,
                    'message' => $hasData ? null : 'Data keuangan untuk ' . $kode . ' belum tersedia. Silakan input manual.',
                    'data' => [
                        'kode_efek' => $stock->kode,
                        'nama_efek' => $stock->nama,
                        'per' => $per,
                        'pbv' => $pbv,
                        'roe' => $roe,
                        'roa' => $roa,
                        'npm' => $npm,
                        'ev_ebitda' => null,
                        'der' => $der,
                        'current_ratio' => $current_ratio,
                        'aktivitas_lancar' => null,
                        'gross_profit_margin' => $gross_profit_margin,
                        'operating_profit_margin' => $operating_profit_margin,
                    ]
                ]);
            }
        } elseif ($type === 'obligasi') {
            $bond = ObligasiBond::where('kode', $kode)->first();
            if ($bond) {
                // ponytail: Hitung metrics dari bond financial data jika ada
                $der = null;
                $current_ratio = null;
                $gross_profit_margin = null;
                $operating_profit_margin = null;
                
                if ($bond->total_equity > 0) {
                    $der = $bond->total_liabilities / $bond->total_equity;
                }
                
                if ($bond->current_liabilities > 0) {
                    $current_ratio = $bond->current_asset / $bond->current_liabilities;
                }
                
                if ($bond->net_revenue > 0 && $bond->cost_of_good_sold) {
                    $gross_profit_margin = (($bond->net_revenue - $bond->cost_of_good_sold) / $bond->net_revenue) * 100;
                }
                
                if ($bond->net_revenue > 0 && $bond->laba_operasional) {
                    $operating_profit_margin = ($bond->laba_operasional / $bond->net_revenue) * 100;
                }
                
                $hasData = $bond->total_asset || $bond->total_liabilities || $bond->total_equity;
                
                return response()->json([
                    'found' => true,
                    'has_financial_data' => $hasData,
                    'message' => $hasData ? null : 'Data keuangan untuk ' . $kode . ' belum tersedia. Silakan input manual.',
                    'data' => [
                        'kode_efek' => $bond->kode,
                        'nama_efek' => $kode,
                        'ytm' => null,
                        'rating' => null,
                        'kupon' => null,
                        'tenor' => null,
                        'durasi' => null,
                        'shadow_rating' => null,
                        'der' => $der,
                        'current_ratio' => $current_ratio,
                        'aktivitas_lancar' => null,
                        'gross_profit_margin' => $gross_profit_margin,
                        'operating_profit_margin' => $operating_profit_margin,
                    ]
                ]);
            }
        }

        return response()->json(['found' => false, 'message' => 'Kode efek ' . $kode . ' tidak ditemukan di database']);
    }

    public function lookupNavHistory(Request $request)
    {
        $request->validate([
            'kode_reksa_dana' => 'required|string|max:20',
            'tanggal' => 'required|date',
        ]);

        $kode = strtoupper(trim($request->kode_reksa_dana));
        $tanggal = $request->tanggal;

        $fund = \App\Models\ReksaDana::whereRaw('UPPER(kode_reksa_dana) = ?', [$kode])->first();

        if (!$fund) {
            return response()->json(['found' => false, 'message' => 'Reksa dana tidak ditemukan']);
        }

        $nav = $fund->harga()->where('tanggal', '<=', $tanggal)->latest('tanggal')->first();

        $snapshot = $fund->snapshots()
            ->where('period_date', '<=', $tanggal)
            ->latest('period_date')
            ->first();

        $data = [
            'nab_per_unit' => $nav?->nab_per_unit ?? $snapshot?->nab_per_unit,
            'aum' => $nav?->aum ?? $snapshot?->aum,
            'unit_participation' => $nav?->unit_participation ?? $snapshot?->total_unit,
            'tanggal_nav' => $nav?->tanggal?->format('Y-m-d'),
            'tanggal_snapshot' => $snapshot?->period_date?->format('Y-m-d'),
        ];

        return response()->json([
            'found' => true,
            'data' => $data,
        ]);
    }
}
