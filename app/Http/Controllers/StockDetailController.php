<?php

namespace App\Http\Controllers;

use App\Models\AnalisaSaham;
use App\Models\Stock;
use App\Models\StockBrokerDocument;
use App\Models\StockBrokerResearch;
use App\Services\AIAnalysisService;
use App\Services\NewsService;
use App\Services\YahooStockDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class StockDetailController extends Controller
{
    public function show(Request $request, Stock $stock)
    {
        $startDate = $this->timeframeStart($request->input('timeframe', '1M'));
        $stock->load(['profile', 'corporateActions', 'financialReports', 'news', 'brokerResearches', 'brokerDocuments']);

        $prices = $stock->prices()
            ->where('tanggal', '>=', $startDate)
            ->get()
            ->map(fn($price) => [
                'tanggal' => $price->tanggal->format('Y-m-d'),
                'open' => $price->open ?? $price->harga,
                'high' => $price->high ?? $price->harga,
                'low' => $price->low ?? $price->harga,
                'close' => $price->close ?? $price->harga,
                'volume' => $price->volume,
            ]);

        if ($prices->isEmpty()) {
            $prices = \App\Models\StockPrice::query()
                ->whereRaw('UPPER(kode_efek) = ?', [strtoupper($stock->kode)])
                ->where('tanggal', '>=', $startDate)
                ->oldest('tanggal')
                ->get()
                ->map(fn($price) => [
                    'tanggal' => $price->tanggal->format('Y-m-d'),
                    'open' => $price->open ?? $price->harga,
                    'high' => $price->high ?? $price->harga,
                    'low' => $price->low ?? $price->harga,
                    'close' => $price->close ?? $price->harga,
                    'volume' => $price->volume,
                ]);
        }

        $legacyResearches = AnalisaSaham::query()
            ->whereRaw('UPPER(kode_saham) = ?', [strtoupper($stock->kode)])
            ->with('brokerResearchDocuments')
            ->get()
            ->flatMap(fn($analysis) => $analysis->brokerResearchDocuments->map(fn($document) => [
                'analysis' => $analysis,
                'document' => $document,
            ]));

        $targets = $stock->brokerResearches->pluck('target_price')->filter()->map(fn($value) => (float) $value);
        $consensus = [
            'highest' => $targets->max(),
            'lowest' => $targets->min(),
            'average' => $targets->isNotEmpty() ? $targets->avg() : null,
            'upside' => $targets->isNotEmpty() && $stock->harga_terbaru
                ? (($targets->avg() - (float) $stock->harga_terbaru) / (float) $stock->harga_terbaru) * 100
                : null,
        ];

        return view('saham.detail', [
            'layout' => $request->routeIs('admin.*') ? 'layouts.admin' : 'layouts.user',
            'routePrefix' => $request->routeIs('admin.*') ? 'admin' : 'user',
            'stock' => $stock,
            'prices' => $prices,
            'legacyResearches' => $legacyResearches,
            'consensus' => $consensus,
            'timeframe' => $request->input('timeframe', '1M'),
        ]);
    }

    public function summarizeNews(Request $request, Stock $stock, AIAnalysisService $service)
    {
        return $this->summarize($request, fn() => $service->summarizeStockNews($stock->id), 'berita');
    }

    public function summarizeBrokerResearch(Request $request, Stock $stock, AIAnalysisService $service)
    {
        return $this->summarize($request, fn() => $service->summarizeBrokerResearch($stock->id), 'riset-broker');
    }

    public function viewResearch(Request $request, Stock $stock, StockBrokerResearch $research)
    {
        $this->ensureResearch($stock, $research);
        abort_if(!$research->pdf_file || !Storage::disk('public')->exists($research->pdf_file), 404);

        return response()->file(Storage::disk('public')->path($research->pdf_file));
    }

    public function downloadResearch(Request $request, Stock $stock, StockBrokerResearch $research)
    {
        $this->ensureResearch($stock, $research);
        abort_if(!$research->pdf_file || !Storage::disk('public')->exists($research->pdf_file), 404);

        return Storage::disk('public')->download($research->pdf_file);
    }

    public function fetchSummary(Request $request, Stock $stock, YahooStockDataService $yahoo, NewsService $news)
    {
        try {
            $data = $yahoo->fetchSummary($stock);

            $googleNews = $news->fetchGoogleNews($stock);
            if (!empty($googleNews)) {
                $existing = $data['news'] ?? [];
                $data['news'] = array_merge($existing, $googleNews);
                usort($data['news'], fn($a, $b) => strcmp($b['publishedAt'] ?? '', $a['publishedAt'] ?? ''));
            }

            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function refreshNews(Request $request, Stock $stock, NewsService $news)
    {
        try {
            $symbol = strtoupper($stock->kode) . '.JK';
            \Illuminate\Support\Facades\Cache::forget("yfapi_summary_v2_{$symbol}");

            $allNews = $news->fetchNews($stock);
            $count = count($allNews);

            return back()
                ->with('success', "{$count} berita terbaru berhasil dimuat.")
                ->with('active_tab', 'berita');
        } catch (\Throwable $e) {
            return back()
                ->with('error', 'Gagal memuat berita: ' . $e->getMessage())
                ->with('active_tab', 'berita');
        }
    }

    public function fetchYahoo(Request $request, Stock $stock, YahooStockDataService $service)
    {
        $range = $request->input('range', '1d');
        $allowed = ['1d', '5d', '1mo', '3mo', '6mo', '1y', '5y', 'max'];
        if (!in_array($range, $allowed)) {
            $range = '1d';
        }

        try {
            $data = $service->fetchYahooData($stock, $range);

            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function syncYahooPrices(Request $request, Stock $stock, YahooStockDataService $service)
    {
        $request->validate([
            'range' => 'nullable|in:5d,1mo,3mo,6mo,ytd,1y,2y,5y',
        ]);

        try {
            $result = $service->syncPrices($stock, $request->input('range', '1y'));

            return back()
                ->with('success', "Sync Yahoo {$result['symbol']} berhasil. {$result['saved']} data harga tersimpan.")
                ->with('active_tab', 'grafik');
        } catch (\Throwable $e) {
            return back()
                ->with('error', 'Sync Yahoo gagal: ' . $e->getMessage())
                ->with('active_tab', 'grafik');
        }
    }

    public function viewBrokerDocument(Request $request, Stock $stock, StockBrokerDocument $document)
    {
        abort_if($document->stock_id !== $stock->id, 404);
        abort_if(!Storage::disk('public')->exists($document->file_path), 404);

        return response()->file(Storage::disk('public')->path($document->file_path), [
            'Content-Disposition' => 'inline; filename="' . $document->original_name . '"',
        ]);
    }

    public function storeBrokerDocument(Request $request, Stock $stock)
    {
        $request->validate([
            'broker_name' => 'required|string|max:255',
            'judul'       => 'required|string|max:255',
            'tanggal'     => 'required|date',
            'dokumen'     => 'required|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx|max:20480',
        ]);

        $file = $request->file('dokumen');
        $path = $file->store('broker-documents', 'public');

        $stock->brokerDocuments()->create([
            'broker_name'   => $request->broker_name,
            'judul'         => $request->judul,
            'tanggal'       => $request->tanggal,
            'file_path'     => $path,
            'original_name' => $file->getClientOriginalName(),
        ]);

        return back()->with('success', 'Dokumen broker berhasil diunggah.')->with('active_tab', 'detail-broker');
    }

    public function deleteBrokerDocument(Request $request, Stock $stock, StockBrokerDocument $document)
    {
        abort_if($document->stock_id !== $stock->id, 404);
        Storage::disk('public')->delete($document->file_path);
        $document->delete();

        return back()->with('success', 'Dokumen berhasil dihapus.')->with('active_tab', 'detail-broker');
    }

    private function summarize(Request $request, callable $callback, string $tab)
    {
        try {
            $callback();

            return back()->with('success', 'AI Summary berhasil dibuat.')->with('active_tab', $tab);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage())->with('active_tab', $tab);
        }
    }

    private function ensureResearch(Stock $stock, StockBrokerResearch $research): void
    {
        abort_if($research->stock_id !== $stock->id, 404);
    }

    public function searchComparison(Request $request)
    {
        $q = $request->get('q');
        if (!$q || strlen($q) < 1) {
            return response()->json([]);
        }

        $stocks = Stock::where('kode', 'like', "%{$q}%")
            ->orWhere('nama', 'like', "%{$q}%")
            ->limit(10)
            ->get(['id', 'kode', 'nama', 'sektor', 'harga_terbaru']);

        return response()->json($stocks);
    }

    public function fetchComparison(Request $request, YahooStockDataService $service)
    {
        $code = $request->get('code');
        $range = $request->get('range', '1y');

        $stock = Stock::where('kode', $code)->first();
        if (!$stock) {
            return response()->json(['success' => false, 'message' => 'Saham tidak ditemukan.'], 404);
        }

        try {
            $data = $service->fetchYahooData($stock, $range);
            return response()->json([
                'success' => true,
                'data' => $data,
                'stock' => ['kode' => $stock->kode, 'nama' => $stock->nama],
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function timeframeStart(string $timeframe): Carbon
    {
        return match ($timeframe) {
            '1D' => now()->subDay(),
            '1W' => now()->subWeek(),
            '3M' => now()->subMonths(3),
            '6M' => now()->subMonths(6),
            'YTD' => now()->startOfYear(),
            '1Y' => now()->subYear(),
            '5Y' => now()->subYears(5),
            'MAX' => now()->subYears(50),
            default => now()->subMonth(),
        };
    }
}
