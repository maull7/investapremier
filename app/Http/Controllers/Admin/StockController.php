<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SyncSahamFromIdxJob;
use App\Models\AnalisaEfek;
use App\Models\AnalisaReksaDana;
use App\Models\Stock;
use App\Models\SyncRun;
use App\Models\ExtractionBatch;
use App\Exports\StocksTemplateExport;
use App\Imports\StocksImport;
use App\Support\ActivityLogger;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'daftar');

        if ($tab === 'efek-reksa-dana') {
            return $this->indexEfekReksaDana($request);
        }

        $perPage = in_array($request->per_page, [10, 25, 50]) ? $request->per_page : 10;
        $query = Stock::latest();

        if ($request->search) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('kode', 'like', "%{$s}%")
                    ->orWhere('nama', 'like', "%{$s}%")
                    ->orWhere('sektor', 'like', "%{$s}%");
            });
        }

        $stocks = $query->paginate($perPage)->withQueryString();
        $extractionBatches = ExtractionBatch::with('creator')
            ->where('data_type', 'stock_daily_transaction')
            ->latest()
            ->paginate(10, ['*'], 'batch_page')
            ->withQueryString();
        $extractionRanges = $this->buildRangeOptions(Stock::count());
        $detailBatch = $request->filled('detail_batch')
            ? ExtractionBatch::with([
                'stockDailyTransactions',
            ])->where('data_type', 'stock_daily_transaction')->find($request->integer('detail_batch'))
            : null;

        $recentSyncRuns = SyncRun::where('type', SyncRun::TYPE_SAHAM_IDX)->latest()->paginate(15, ['*'], 'runs_page');
        $selectedRunId = $request->integer('selected_run') ?: $recentSyncRuns->first()?->id;
        $selectedRun = $selectedRunId ? SyncRun::find($selectedRunId) : null;
        $changesUrl = $selectedRun ? route('admin.saham.sync-idx.changes', $selectedRun) : null;
        $detailTypes = [];

        $lastSyncRun = SyncRun::where('type', SyncRun::TYPE_SAHAM_IDX)
            ->where('status', SyncRun::STATUS_COMPLETED)
            ->latest()
            ->first();

        return view('admin.stocks.index', compact('stocks', 'perPage', 'tab', 'extractionBatches', 'detailBatch', 'extractionRanges', 'recentSyncRuns', 'selectedRun', 'changesUrl', 'detailTypes', 'lastSyncRun'));
    }

    public function create()
    {
        return view('admin.stocks.form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode' => 'required|string|max:10|unique:stocks,kode',
            'nama' => 'required|string|max:255',
            'sektor' => 'nullable|string|max:255',
            'sub_industri' => 'nullable|string|max:255',
            'harga_terbaru' => 'nullable|numeric',
            'harga_penutupan_sebelumnya' => 'nullable|numeric',
            'harga_pembukaan' => 'nullable|numeric',
            'harga_tertinggi' => 'nullable|numeric',
            'harga_terendah' => 'nullable|numeric',
            'volume' => 'nullable|numeric',
            'value' => 'nullable|numeric',
            'frekuensi' => 'nullable|numeric',
            'jumlah_saham' => 'nullable|numeric',
            'market_capital' => 'nullable|numeric',
            'last_update' => 'nullable|date',
        ]);

        $stock = Stock::create($request->all());

        ActivityLogger::log(
            'Membuat Saham',
            "Saham {$stock->kode} - {$stock->nama} berhasil ditambahkan",
            'success',
            $stock,
        );

        return redirect()->route('admin.saham.index')->with('success', 'Saham berhasil ditambahkan.');
    }

    public function edit(Stock $stock)
    {
        return view('admin.stocks.form', compact('stock'));
    }

    public function update(Request $request, Stock $stock)
    {
        $request->validate([
            'kode' => 'required|string|max:10|unique:stocks,kode,' . $stock->id,
            'nama' => 'required|string|max:255',
            'sektor' => 'nullable|string|max:255',
            'sub_industri' => 'nullable|string|max:255',
            'harga_terbaru' => 'nullable|numeric',
            'harga_penutupan_sebelumnya' => 'nullable|numeric',
            'harga_pembukaan' => 'nullable|numeric',
            'harga_tertinggi' => 'nullable|numeric',
            'harga_terendah' => 'nullable|numeric',
            'volume' => 'nullable|numeric',
            'value' => 'nullable|numeric',
            'frekuensi' => 'nullable|numeric',
            'jumlah_saham' => 'nullable|numeric',
            'market_capital' => 'nullable|numeric',
            'last_update' => 'nullable|date',
        ]);

        $stock->update($request->all());

        ActivityLogger::log(
            'Mengubah Saham',
            "Saham {$stock->kode} - {$stock->nama} berhasil diperbarui",
            'success',
            $stock,
        );

        return redirect()->route('admin.saham.index')->with('success', 'Saham berhasil diperbarui.');
    }

    public function destroy(Stock $stock)
    {
        ActivityLogger::log(
            'Menghapus Saham',
            "Saham {$stock->kode} - {$stock->nama} berhasil dihapus",
            'success',
            $stock,
        );
        $stock->delete();
        return redirect()->route('admin.saham.index')->with('success', 'Saham berhasil dihapus.');
    }

    public function downloadTemplate()
    {
        return Excel::download(new StocksTemplateExport, 'template-saham.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv']);

        $import = new StocksImport;
        Excel::import($import, $request->file('file'));

        ActivityLogger::log(
            'Import Saham',
            "{$import->imported} data saham berhasil diimport dari Excel",
            'success',
        );

        return redirect()->route('admin.saham.index')
            ->with('success', "{$import->imported} data saham berhasil diimport dari Excel.");
    }

    protected function indexEfekReksaDana(Request $request)
    {
        $perPage = in_array($request->per_page, [10, 25, 50]) ? $request->per_page : 10;

        $query = AnalisaEfek::select(
            'analisa_efek.kode_efek',
            'analisa_efek.nama_efek',
            'analisa_efek.sektor',
        )
            ->selectRaw('COUNT(DISTINCT analisa_efek.analisa_reksa_dana_id) as total_reksa_dana')
            ->selectRaw('COUNT(DISTINCT reksa_dana.nama_manajer_investasi) as total_mi')
            ->selectRaw('SUM(analisa_efek.nilai_pasar) as total_nilai_pasar')
            ->join('analisa_reksa_dana', 'analisa_efek.analisa_reksa_dana_id', '=', 'analisa_reksa_dana.id')
            ->join('reksa_dana', 'analisa_reksa_dana.reksa_dana_id', '=', 'reksa_dana.id')
            ->groupBy('analisa_efek.kode_efek', 'analisa_efek.nama_efek', 'analisa_efek.sektor');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('analisa_efek.kode_efek', 'like', "%{$s}%")
                    ->orWhere('analisa_efek.nama_efek', 'like', "%{$s}%");
            });
        }

        if ($request->filled('ffs_bulan')) {
            $query->where('analisa_reksa_dana.ffs_bulan', $request->ffs_bulan);
        }

        if ($request->filled('ffs_tahun')) {
            $query->where('analisa_reksa_dana.ffs_tahun', $request->ffs_tahun);
        }

        $efeks = $query->orderBy('total_nilai_pasar', 'desc')->paginate($perPage)->withQueryString();

        $tahunList = AnalisaReksaDana::where('product_type', 'reksa_dana')
            ->whereNotNull('ffs_tahun')
            ->distinct()->orderBy('ffs_tahun', 'desc')->pluck('ffs_tahun');

        $bulanIndonesia = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        return view('admin.stocks.index', compact('efeks', 'tahunList', 'bulanIndonesia', 'perPage') + ['tab' => 'efek-reksa-dana']);
    }

    private function buildRangeOptions(int $total, int $step = 20): array
    {
        $ranges = [];

        for ($start = 1; $start <= $total; $start += $step) {
            $end = min($start + $step - 1, $total);
            $ranges[] = [
                'value' => "{$start}-{$end}",
                'label' => "{$start} - {$end}",
            ];
        }

        return $ranges;
    }

    /**
     * One-click sync saham. Dispatch job (non-blocking).
     * Job akan pake BackendSyncService kalau BACKEND_SYNC_URL dikonfigurasi.
     */
    public function syncFromIdx(Request $request)
    {
        $inflight = SyncRun::where('type', SyncRun::TYPE_SAHAM_IDX)
            ->whereIn('status', [SyncRun::STATUS_QUEUED, SyncRun::STATUS_RUNNING])
            ->where('updated_at', '>=', now()->subMinutes(10))
            ->latest()
            ->first();

        if ($inflight) {
            $payload = [
                'run_id' => $inflight->id,
                'status' => $inflight->status,
                'reused' => true,
            ];
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json($payload);
            }
            return redirect()->route('admin.saham.index')
                ->with('sync_run_id', $inflight->id);
        }

        $run = SyncRun::create([
            'type' => SyncRun::TYPE_SAHAM_IDX,
            'status' => SyncRun::STATUS_QUEUED,
            'current_step' => 'queued',
            'current_step_label' => 'Menunggu worker mengambil job dari antrian',
            'progress_percent' => 0,
            'user_id' => $request->user()?->id,
        ]);

        SyncSahamFromIdxJob::dispatch($run->id);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'run_id' => $run->id,
                'status' => $run->status,
            ]);
        }

        return redirect()->route('admin.saham.index')
            ->with('sync_run_id', $run->id)
            ->with('success', 'Sync saham dimulai. Modal akan menampilkan progress real-time.');
    }

    /**
     * JSON endpoint polled by the frontend modal to track sync progress.
     */
    public function syncStatus(SyncRun $run)
    {
        return response()->json([
            'id' => $run->id,
            'type' => $run->type,
            'status' => $run->status,
            'current_step' => $run->current_step,
            'current_step_label' => $run->current_step_label,
            'progress_percent' => $run->progress_percent,
            'message' => $run->message,
            'errors' => $run->errors,
            'stats' => $run->stats,
            'is_terminal' => $run->isTerminal(),
            'started_at' => $run->started_at?->toIso8601String(),
            'completed_at' => $run->completed_at?->toIso8601String(),
        ]);
    }

    public function syncChanges(SyncRun $run, \Illuminate\Http\Request $request)
    {
        $query = \App\Models\SyncChangeLog::where('sync_run_id', $run->id);

        if ($request->filled('entity_type')) {
            $query->where('entity_type', $request->entity_type);
        }

        $changes = $query->orderBy('created_at')->orderBy('id')
            ->paginate($request->per_page ?? 50);

        return response()->json($changes);
    }
}
