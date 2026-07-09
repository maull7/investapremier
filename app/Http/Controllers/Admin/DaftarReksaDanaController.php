<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ReplaceRewriteReksaDanaJob;
use App\Jobs\SyncAllPasardanaJob;
use App\Jobs\SyncReksaDanaFromPasardanaJob;
use App\Models\DataSourceLink;
use App\Models\DataSourceSyncLog;
use App\Models\DocumentParsedPage;
use App\Models\DocumentPartition;
use App\Models\FfsExtractionResult;
use App\Models\HargaReksaDana;
use App\Models\InvestmentManager;
use App\Models\MutualFundAssetAllocation;
use App\Models\MutualFundPortfolioComposition;
use App\Models\ReksaDana;
use App\Models\ReksaDanaDocument;
use App\Models\SyncRun;
use App\Services\KodeReksaDanaParser;
use App\Services\InvestmentPersonService;
use App\Services\ReksaDanaChartDataService;
use App\Services\FfsExtractionService;
use App\Services\ProspektusParserService;
use App\Services\DocumentDataExtractorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Support\ActivityLogger;

class DaftarReksaDanaController extends Controller
{
    private const JENIS_OPTIONS = ['Saham', 'Pendapatan Tetap', 'Campuran', 'Pasar Uang', 'Terproteksi', 'Global', 'DIRE-DINFRA', 'Penyertaan terbatas'];
    private const KATEGORI_OPTIONS = ['Konvensional', 'Syariah', 'index', 'ETF'];
    private const KATEGORI_PRODUK_OPTIONS = ['Konvensional', 'Syariah', 'Index', 'ETF'];

    public function index(Request $request)
    {
        $tab = $request->get('tab', 'harga');

        $hargaSort = $request->get('sort', 'nama_reksa_dana');
        $hargaDir = $request->get('direction', 'asc');
        $hargaQuery = ReksaDana::orderBy($hargaSort, $hargaDir);
        if ($request->jenis) $hargaQuery->where('jenis', $request->jenis);
        if ($request->search) $hargaQuery->where('nama_reksa_dana', 'like', '%' . $request->search . '%');
        if ($request->harga_tanggal) $hargaQuery->whereDate('tanggal_nab', $request->harga_tanggal);
        $reksaDanas = $hargaQuery->paginate(20, ['*'], 'harga_page')->withQueryString();

        $harianTanggal = $request->get('harian_tanggal');
        $harianSort = $request->get('harian_sort', 'nama_reksa_dana');
        $harianDir = $request->get('harian_direction', 'asc');
        $harianQuery = ReksaDana::orderBy($harianSort, $harianDir);
        if ($harianTanggal) {
            $harianQuery->whereDate('tanggal_nab', $harianTanggal);
        }
        if ($request->search) {
            $harianQuery->where('nama_reksa_dana', 'like', '%' . $request->search . '%');
        }
        $harian = $harianQuery->paginate(20, ['*'], 'harian_page')->withQueryString();

        $dataSourceLinks = collect();
        $syncLogs = collect();
        $reksaDanaList = collect();
        $reksaDanaOptions = ReksaDana::orderBy('nama_reksa_dana')->get(['id', 'kode_reksa_dana', 'nama_reksa_dana', 'nama_manajer_investasi', 'jenis']);

        $editingLink = null;
        $documents = collect();
        $documentFunds = collect();
        $lastSyncRun = null;

        if ($tab === 'link-website') {
            $linkQuery = DataSourceLink::global()->with(['reksaDana', 'urls'])->latest();
            if ($request->search) {
                $linkQuery->where(function ($q) use ($request) {
                    $q->where('nama_sumber', 'like', '%' . $request->search . '%')
                        ->orWhereHas('reksaDana', fn ($r) => $r->where('nama_reksa_dana', 'like', '%' . $request->search . '%'));
                });
            }
            if ($request->jenis_akses) {
                $linkQuery->where('jenis_akses', $request->jenis_akses);
            }
            $dataSourceLinks = $linkQuery->paginate(15, ['*'], 'link_page')->withQueryString();

            $syncLogs = DataSourceSyncLog::with(['link.reksaDana', 'user'])
                ->latest()
                ->paginate(10, ['*'], 'log_page')
                ->withQueryString();

            $reksaDanaList = ReksaDana::orderBy('nama_reksa_dana')->get(['id', 'nama_reksa_dana']);

            if ($request->edit) {
                $editingLink = DataSourceLink::with('urls')->find($request->edit);
            }
        }

        if ($tab === 'prospektus-ffs') {
            $documentFundQuery = ReksaDana::with([
                'documents' => fn($q) => $q->with('uploader')->withCount('parsedPages')
            ])->orderBy('nama_reksa_dana');

            if ($request->search) {
                $documentFundQuery->where(function ($query) use ($request) {
                    $query
                        ->where('nama_reksa_dana', 'like', '%' . $request->search . '%')
                        ->orWhere('kode_reksa_dana', 'like', '%' . $request->search . '%')
                        ->orWhere('nama_manajer_investasi', 'like', '%' . $request->search . '%');
                });
            }

            if ($request->jenis) {
                $documentFundQuery->where('jenis', $request->jenis);
            }

            $documentFunds = $documentFundQuery->paginate(10, ['*'], 'document_page')->withQueryString();

            $reksaDanaList = ReksaDana::orderBy('nama_reksa_dana')->get(['id', 'kode_reksa_dana', 'nama_reksa_dana']);
        }

        $lastSyncRun = SyncRun::whereIn('type', [
            SyncRun::TYPE_RD_PASARDANA,
            SyncRun::TYPE_RD_HARGA_HARIAN,
            SyncRun::TYPE_ALL_PASARDANA,
            SyncRun::TYPE_RELASI_MI_RD,
        ])->where('status', SyncRun::STATUS_COMPLETED)->latest()->first();

        $hargaTanggal = $request->get('harga_tanggal');

        $recentSyncRuns = SyncRun::whereIn('type', [
            SyncRun::TYPE_RD_HARGA_HARIAN,
            SyncRun::TYPE_ALL_PASARDANA,
            SyncRun::TYPE_RELASI_MI_RD,
        ])->latest()->paginate(15, ['*'], 'runs_page');
        $selectedRunId = $request->integer('selected_run') ?: $recentSyncRuns->first()?->id;
        $selectedRun = $selectedRunId ? SyncRun::find($selectedRunId) : null;
        $changesUrl = $selectedRun ? route('admin.daftar-reksa-dana.sync-pasardana.changes', $selectedRun) : null;
        $detailTypes = [
            'rd' => 'Reksa Dana',
            'rd_harian' => 'Harga Harian RD',
        ];

        return view('admin.daftar-reksa-dana.index', compact(
            'reksaDanas', 'harian', 'tab',
            'dataSourceLinks', 'syncLogs', 'reksaDanaList', 'editingLink',
            'reksaDanaOptions',
            'documents', 'documentFunds',
            'harianTanggal', 'hargaTanggal',
            'recentSyncRuns', 'selectedRun', 'changesUrl', 'detailTypes',
            'lastSyncRun',
        ));
    }

    public function storeDocument(Request $request)
    {
        $validated = $request->validate([
            'reksa_dana_id' => 'required|exists:reksa_dana,id',
            'document_type' => 'required|in:prospektus,ffs',
            'prospektus_month' => 'required_if:document_type,prospektus|nullable|integer|min:1|max:12',
            'prospektus_year' => 'required_if:document_type,prospektus|nullable|integer|min:2000|max:2100',
            'ffs_month' => 'required_if:document_type,ffs|nullable|integer|min:1|max:12',
            'ffs_year' => 'required_if:document_type,ffs|nullable|integer|min:2000|max:2100',
            'file' => 'required|file|mimes:pdf|max:20480',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Untuk prospektus, simpan tahun & bulan ke ffs_year & ffs_month sebelum duplicate check
        if ($validated['document_type'] === 'prospektus') {
            if (!empty($validated['prospektus_year'])) {
                $validated['ffs_year'] = $validated['prospektus_year'];
            }
            if (!empty($validated['prospektus_month'])) {
                $validated['ffs_month'] = $validated['prospektus_month'];
            }
        }
        unset($validated['prospektus_year']);
        unset($validated['prospektus_month']);

        // ponytail: duplicate check at the shared method, not in callers
        $existing = $this->findExistingDocument($validated);
        if ($existing) {
            return redirect()->route('admin.daftar-reksa-dana.index', ['tab' => 'prospektus-ffs'])
                ->with('error', 'Dokumen untuk periode tersebut sudah ada. Silakan edit dokumen yang sudah ada.');
        }

        $file = $request->file('file');
        $filename = now()->format('Ymd-His') . '-' . Str::random(10) . '.pdf';
        $path = $file->storeAs('reksa-dana-documents/' . $validated['reksa_dana_id'], $filename, 'public');

        ReksaDanaDocument::create([
            ...$validated,
            'uploaded_by' => $request->user()->id,
            'original_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
        ]);

        ActivityLogger::log(
            'Upload Dokumen',
            "Dokumen {$validated['document_type']} berhasil diupload untuk reksa dana ID {$validated['reksa_dana_id']}",
            'success',
        );

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Dokumen berhasil diupload.']);
        }

        return redirect()->route('admin.daftar-reksa-dana.index', ['tab' => 'prospektus-ffs'])
            ->with('success', 'Dokumen berhasil diupload.');
    }

    public function viewDocument(ReksaDanaDocument $document)
    {
        $this->ensureDocumentExists($document);

        return response()->file(Storage::disk('public')->path($document->file_path), [
            'Content-Type' => $document->mime_type ?: 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . addslashes($document->original_name) . '"',
        ]);
    }

    public function downloadDocument(ReksaDanaDocument $document)
    {
        $this->ensureDocumentExists($document);

        return Storage::disk('public')->download($document->file_path, $document->original_name);
    }

    public function updateDocument(Request $request, ReksaDanaDocument $document)
    {
        $validated = $request->validate([
            'document_type' => 'required|in:prospektus,ffs',
            'ffs_month' => 'nullable|integer|min:1|max:12',
            'ffs_year' => 'required|integer|min:2000|max:2100',
            'notes' => 'nullable|string|max:1000',
            'file' => 'nullable|file|mimes:pdf|max:20480',
        ]);

        // ponytail: same duplicate guard, exclude self
        $existing = $this->findExistingDocument(['reksa_dana_id' => $document->reksa_dana_id, ...$validated], $document->id);
        if ($existing) {
            return redirect()->route('admin.daftar-reksa-dana.index', ['tab' => 'prospektus-ffs'])
                ->with('error', 'Dokumen untuk periode tersebut sudah ada. Silakan edit dokumen yang sudah ada.');
        }

        unset($validated['file']);

        if ($request->hasFile('file')) {
            $document->deleteStoredFile();

            $file = $request->file('file');
            $filename = now()->format('Ymd-His') . '-' . Str::random(10) . '.pdf';
            $path = $file->storeAs('reksa-dana-documents/' . $document->reksa_dana_id, $filename, 'public');

            $validated['original_name'] = $file->getClientOriginalName();
            $validated['file_path'] = $path;
            $validated['mime_type'] = $file->getClientMimeType();
            $validated['file_size'] = $file->getSize();
        }

        $document->update($validated);

        ActivityLogger::log(
            'Mengubah Dokumen',
            "Dokumen {$document->original_name} berhasil diperbarui",
            'success',
            $document,
        );

        return redirect()->route('admin.daftar-reksa-dana.index', ['tab' => 'prospektus-ffs'])
            ->with('success', 'Dokumen berhasil diperbarui.');
    }

    public function destroyDocument(ReksaDanaDocument $document)
    {
        ActivityLogger::log(
            'Menghapus Dokumen',
            "Dokumen {$document->original_name} berhasil dihapus",
            'success',
            $document,
        );

        $document->deleteStoredFile();
        $document->delete();

        return redirect()->route('admin.daftar-reksa-dana.index', ['tab' => 'prospektus-ffs'])
            ->with('success', 'Dokumen berhasil dihapus.');
    }

    public function checkDocumentExists(Request $request)
    {
        $validated = $request->validate([
            'reksa_dana_id' => 'required|exists:reksa_dana,id',
            'document_type' => 'required|in:prospektus,ffs',
            'ffs_month' => 'nullable|integer|min:1|max:12',
            'ffs_year' => 'required|integer|min:2000|max:2100',
        ]);

        $document = $this->findExistingDocument($validated);

        return response()->json([
            'exists' => $document !== null,
            'document' => $document ? [
                'id' => $document->id,
                'original_name' => $document->original_name,
                'document_type' => $document->document_type,
                'ffs_month' => $document->ffs_month,
                'ffs_year' => $document->ffs_year,
                'notes' => $document->notes,
                'updated_at' => $document->updated_at?->format('Y-m-d H:i:s'),
            ] : null,
        ]);
    }

    private function findExistingDocument(array $params, ?int $excludeId = null): ?ReksaDanaDocument
    {
        $query = ReksaDanaDocument::where('reksa_dana_id', $params['reksa_dana_id'])
            ->where('document_type', $params['document_type']);

        if (!empty($params['ffs_month'])) {
            $query->where('ffs_month', $params['ffs_month'])
                  ->where('ffs_year', $params['ffs_year']);
        } else {
            $query->whereNull('ffs_month')
                  ->where('ffs_year', $params['ffs_year']);
        }

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->first();
    }

    private function ensureDocumentExists(ReksaDanaDocument $document): void
    {
        abort_if(!$document->file_path || !Storage::disk('public')->exists($document->file_path), 404, 'Dokumen tidak ditemukan.');
    }

    public function show($id, ReksaDanaChartDataService $chartDataService)
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
        $chartData = $chartDataService->forFund(
            $fund,
            $range,
            request('from_date'),
            request('to_date')
        );

        $navHistoryQuery = $fund->harga()->orderBy('tanggal');
        if ($chartData['from']) {
            $navHistoryQuery->whereDate('tanggal', '>=', $chartData['from']);
        }
        if ($chartData['to']) {
            $navHistoryQuery->whereDate('tanggal', '<=', $chartData['to']);
        }

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
            $topHoldings = $fund->portfolioCompositions()
                ->where('period_date', $latestPeriodDate)
                ->orderByDesc('weight_percent')
                ->get();
        }

        $portfolioTimeline = $fund->portfolioCompositions()
            ->selectRaw('reksa_dana_id, period_date, security_name, security_type, weight_percent')
            ->orderBy('period_date')
            ->get()
            ->groupBy('period_date');

        $latestNav = $navHistory->last();
        $firstNav = $navHistory->first();
        $returnDaily = null;
        $returnMonthly = null;
        $returnYearly = null;

        // — NAV-based returns (when history has at least 2 points) —
        if ($latestNav && $firstNav && $firstNav->nab_per_unit > 0 && $navHistory->count() > 1) {
            $returnYearly = (($latestNav->nab_per_unit - $firstNav->nab_per_unit) / $firstNav->nab_per_unit) * 100;
        }

        $prevDayNav = null;
        if ($latestNav) {
            $prevDayNav = $fund->harga()->where('tanggal', '<', $latestNav->tanggal)->orderByDesc('tanggal')->first();
        }
        if ($latestNav && $prevDayNav && $prevDayNav->nab_per_unit > 0) {
            $returnDaily = (($latestNav->nab_per_unit - $prevDayNav->nab_per_unit) / $prevDayNav->nab_per_unit) * 100;
        }

        $prevMonthNav = $fund->harga()->where('tanggal', '<=', now()->subMonth())->orderByDesc('tanggal')->first();
        if ($latestNav && $prevMonthNav && $prevMonthNav->nab_per_unit > 0) {
            $returnMonthly = (($latestNav->nab_per_unit - $prevMonthNav->nab_per_unit) / $prevMonthNav->nab_per_unit) * 100;
        }

        // — Fallback to Pasardana/FFS return fields when NAV history can't compute —
        if ($returnDaily === null && $fund->return_1d !== null) {
            $returnDaily = (float) $fund->return_1d * 100;
        }
        if ($returnMonthly === null && $fund->return_1m !== null) {
            $returnMonthly = (float) $fund->return_1m * 100;
        }
        if ($returnYearly === null && $fund->return_1y !== null) {
            $returnYearly = (float) $fund->return_1y * 100;
        }



        // — Pasardana risk metrics —
        $riskMetrics = [
            'sharpe_ratio_1y' => $fund->sharpe_ratio_1y,
            'sharpe_ratio_3y' => $fund->sharpe_ratio_3y,
            'sharpe_ratio_5y' => $fund->sharpe_ratio_5y,
            'stdev_1y'        => $fund->stdev_1y,
            'stdev_3y'        => $fund->stdev_3y,
            'stdev_5y'        => $fund->stdev_5y,
            'beta_1y'         => $fund->beta_1y,
            'beta_3y'         => $fund->beta_3y,
            'beta_5y'         => $fund->beta_5y,
            'max_drawdown_1y' => $fund->max_drawdown_1y,
            'max_drawdown_3y' => $fund->max_drawdown_3y,
            'max_drawdown_5y' => $fund->max_drawdown_5y,
        ];

        if ($period = request('period')) {
            $aa = $fund->assetAllocations()->where('period_date', $period)->first();
            $holdings = $fund->portfolioCompositions()->where('period_date', $period)->get();
            $topHoldingsText = $holdings->map(fn($h) => trim("{$h->security_name}:{$h->weight_percent}:{$h->security_type}"))->implode("\n");

            return response()->json([
                'aa' => $aa ? ['equity_percent' => $aa->equity_percent, 'bond_percent' => $aa->bond_percent, 'money_market_percent' => $aa->money_market_percent, 'cash_percent' => $aa->cash_percent] : null,
                'top_holdings_text' => $topHoldingsText,
                'nab_per_unit' => $fund->nab_per_unit,
                'tanggal_nab' => $fund->tanggal_nab?->format('Y-m-d'),
                'aum' => $fund->aum,
                'total_unit' => $fund->total_unit,
                'return_ytd' => $fund->return_ytd,
                'return_1y' => $fund->return_1y,
                'return_1m' => $fund->return_1m,
                'return_inception' => $fund->return_inception,
            ]);
        }

        return view('admin.daftar-reksa-dana.show', compact(
            'fund', 'navHistory', 'navLabels', 'navValues', 'aumValues', 'upValues',
            'aaTimeline', 'aaLabels', 'topHoldings', 'portfolioTimeline',
            'latestNav', 'returnDaily', 'returnMonthly', 'returnYearly', 'range',
            'chartData', 'riskMetrics',
        ));
    }

    public function exportInvestmentManager(ReksaDana $reksaDana, InvestmentPersonService $personService)
    {
        $reksaDana->load('managementTeams');

        if (!filled($reksaDana->nama_manajer_investasi) && !$reksaDana->investment_manager_id) {
            return back()->withErrors([
                'export_investment_manager' => 'Nama Manajer Investasi pada Reksa Dana belum tersedia.',
            ]);
        }

        $manager = $reksaDana->investmentManager
            ?: InvestmentManager::firstOrCreate(['name' => trim((string) $reksaDana->nama_manajer_investasi)]);

        if (!$reksaDana->investment_manager_id) {
            $reksaDana->update(['investment_manager_id' => $manager->id]);
        }

        $committee = $reksaDana->managementTeams
            ->where('type', 'committee')
            ->map(fn ($row) => trim($row->name . ($row->position ? ' - ' . $row->position : '')))
            ->implode("\n");
        $team = $reksaDana->managementTeams
            ->where('type', 'investment_manager')
            ->map(fn ($row) => trim($row->name . ($row->position ? ' - ' . $row->position : '')))
            ->implode("\n");

        $updates = [];
        if ($committee !== '') {
            $updates['investment_committee'] = $this->mergePeopleText($manager->investment_committee, $committee, $personService);
        }
        if ($team !== '') {
            $updates['investment_management_team'] = $this->mergePeopleText($manager->investment_management_team, $team, $personService);
        }

        if ($updates !== []) {
            $manager->update($updates);
        }

        $personService->syncFund($reksaDana->refresh(), 'ffs');
        $personService->syncInvestmentManager($manager->refresh(), 'export_reksa_dana');

        ActivityLogger::log(
            'Export Reksa Dana ke Manajer Investasi',
            "{$reksaDana->nama_reksa_dana} berhasil diekspor ke {$manager->name}",
            'success',
            $manager,
        );

        return redirect()->route('admin.investment-managers.show', $manager)
            ->with('success', 'Data Manajer Investasi berhasil diupdate dari Reksa Dana.');
    }

    public function storeHarga(Request $request)
    {
        if ($request->has('kategori') && is_string($request->input('kategori'))) {
            $decoded = json_decode($request->input('kategori'), true);
            if (is_array($decoded)) {
                $request->merge(['kategori' => $decoded]);
            } elseif ($request->input('kategori') === '') {
                $request->merge(['kategori' => []]);
            }
        }

        $validated = $request->validate([
            'kode_reksa_dana'       => 'nullable|string|max:20|unique:reksa_dana,kode_reksa_dana',
            'nama_reksa_dana'       => 'required|string|max:255',
            'nama_manajer_investasi'=> 'nullable|string|max:255',
            'jenis'                 => 'nullable|string|in:' . implode(',', self::JENIS_OPTIONS),
            'kategori'              => 'nullable|array',
            'kategori.*'            => 'string|in:' . implode(',', self::KATEGORI_OPTIONS),
            'kategori_produk'       => 'nullable|string|in:' . implode(',', self::KATEGORI_PRODUK_OPTIONS),
            'kelas'                 => 'nullable|string|max:10',
            'benchmark'             => 'nullable|string|max:255',
            'tujuan_investasi'      => 'nullable|string',
            'kebijakan_investasi'   => 'nullable|string',
            'mata_uang'             => 'nullable|string|max:10',
            'nab_per_unit'          => 'nullable|numeric',
            'tanggal_nab'           => 'nullable|date',
        ]);

        if (!empty($validated['kode_reksa_dana'])) {
            $parser = app(KodeReksaDanaParser::class);

            if (!$parser->isValidKode($validated['kode_reksa_dana'])) {
                unset($validated['kode_reksa_dana']);
            } else {
                $parsedFromKode = $parser->databaseAttributes($validated['kode_reksa_dana']);
                foreach ($parsedFromKode as $key => $value) {
                    if (empty($validated[$key])) {
                        $validated[$key] = $value;
                    }
                }
            }
        }

        $validated['kategori'] = $validated['kategori'] ?? [];
        $validated['mata_uang'] = $validated['mata_uang'] ?? 'IDR';

        $reksaDana = ReksaDana::create($validated);

        if (!empty($validated['nab_per_unit']) && !empty($validated['tanggal_nab'])) {
            HargaReksaDana::updateOrCreate(
                ['reksa_dana_id' => $reksaDana->id, 'tanggal' => $validated['tanggal_nab']],
                ['nab_per_unit' => $validated['nab_per_unit']]
            );
        }

        ActivityLogger::log(
            'Membuat Reksa Dana',
            "Reksa dana {$validated['nama_reksa_dana']} berhasil ditambahkan",
            'success',
            $reksaDana,
        );

        return redirect()->route('admin.daftar-reksa-dana.index', ['tab' => 'harga'])
            ->with('success', 'Reksa dana berhasil ditambahkan.');
    }

    public function updateHarga(Request $request, ReksaDana $reksaDana)
    {
        // Decode kategori jika dikirim sebagai JSON string dari JS
        if ($request->has('kategori') && is_string($request->input('kategori'))) {
            $decoded = json_decode($request->input('kategori'), true);
            if (is_array($decoded)) {
                $request->merge(['kategori' => $decoded]);
            } elseif ($request->input('kategori') === '') {
                $request->merge(['kategori' => []]);
            }
        }

        $validated = $request->validate([
            'kode_reksa_dana'       => 'nullable|string|max:20|unique:reksa_dana,kode_reksa_dana,' . $reksaDana->id,
            'nama_reksa_dana'       => 'required|string|max:255',
            'nama_manajer_investasi'=> 'nullable|string|max:255',
            'jenis'                 => 'nullable|string|in:' . implode(',', self::JENIS_OPTIONS),
            'kategori'              => 'nullable|array',
            'kategori.*'            => 'string',
            'kategori_produk'       => 'nullable|string|in:' . implode(',', self::KATEGORI_PRODUK_OPTIONS),
            'kelas'                 => 'nullable|string|max:10',
            'benchmark'             => 'nullable|string|max:255',
            'tujuan_investasi'      => 'nullable|string',
            'kebijakan_investasi'   => 'nullable|string',
            'mata_uang'             => 'nullable|string|max:10',
            'nab_per_unit'          => 'nullable|numeric',
            'tanggal_nab'           => 'nullable|date',
        ]);

        if (!empty($validated['kode_reksa_dana'])) {
            $parser = app(KodeReksaDanaParser::class);

            if (!$parser->isValidKode($validated['kode_reksa_dana'])) {
                // Kode tidak valid → jangan simpan, nanti di-regenerate
                unset($validated['kode_reksa_dana']);
            } else {
                $parsedFromKode = $parser->databaseAttributes($validated['kode_reksa_dana']);
                foreach ($parsedFromKode as $key => $value) {
                    if (empty($validated[$key])) {
                        $validated[$key] = $value;
                    }
                }
            }
        }

        $validated['kategori'] = $validated['kategori'] ?? [];

        $reksaDana->update($validated);

        if (!empty($validated['nab_per_unit']) && !empty($validated['tanggal_nab'])) {
            HargaReksaDana::updateOrCreate(
                ['reksa_dana_id' => $reksaDana->id, 'tanggal' => $validated['tanggal_nab']],
                ['nab_per_unit' => $validated['nab_per_unit']]
            );
        }

        ActivityLogger::log(
            'Mengubah Reksa Dana',
            "Reksa dana {$reksaDana->nama_reksa_dana} berhasil diperbarui",
            'success',
            $reksaDana,
        );

        return redirect()->route('admin.daftar-reksa-dana.index', ['tab' => 'harga'])
            ->with('success', 'Reksa dana berhasil diperbarui.');
    }

    public function destroyHarga(ReksaDana $reksaDana)
    {
        ActivityLogger::log(
            'Menghapus Reksa Dana',
            "Reksa dana {$reksaDana->nama_reksa_dana} berhasil dihapus",
            'success',
            $reksaDana,
        );

        $reksaDana->delete();

        return redirect()->route('admin.daftar-reksa-dana.index', ['tab' => 'harga'])
            ->with('success', 'Reksa dana berhasil dihapus.');
    }

    public function storeHarian(Request $request)
    {
        $validated = $request->validate([
            'reksa_dana_id' => 'required|exists:reksa_dana,id',
            'tanggal'       => 'required|date',
            'nab_per_unit'  => 'required|numeric',
        ]);

        $exists = HargaReksaDana::where('reksa_dana_id', $validated['reksa_dana_id'])
            ->where('tanggal', $validated['tanggal'])
            ->exists();

        if ($exists) {
            return redirect()->route('admin.daftar-reksa-dana.index', ['tab' => 'harian'])
                ->with('error', 'Data untuk reksa dana dan tanggal tersebut sudah ada.');
        }

        HargaReksaDana::create($validated);

        ReksaDana::where('id', $validated['reksa_dana_id'])->update([
            'nab_per_unit' => $validated['nab_per_unit'],
            'tanggal_nab'  => $validated['tanggal'],
        ]);

        ActivityLogger::log(
            'Membuat Data Harian',
            "Data harian untuk reksa dana ID {$validated['reksa_dana_id']} tanggal {$validated['tanggal']} berhasil ditambahkan",
            'success',
        );

        return redirect()->route('admin.daftar-reksa-dana.index', ['tab' => 'harian'])
            ->with('success', 'Data harian berhasil ditambahkan.');
    }

    public function updateHarian(Request $request, HargaReksaDana $hargaReksaDana)
    {
        $validated = $request->validate([
            'reksa_dana_id' => 'required|exists:reksa_dana,id',
            'tanggal'       => 'required|date',
            'nab_per_unit'  => 'required|numeric',
        ]);

        $exists = HargaReksaDana::where('reksa_dana_id', $validated['reksa_dana_id'])
            ->where('tanggal', $validated['tanggal'])
            ->where('id', '!=', $hargaReksaDana->id)
            ->exists();

        if ($exists) {
            return redirect()->route('admin.daftar-reksa-dana.index', ['tab' => 'harian'])
                ->with('error', 'Data untuk reksa dana dan tanggal tersebut sudah ada.');
        }

        $hargaReksaDana->update($validated);

        ReksaDana::where('id', $validated['reksa_dana_id'])->update([
            'nab_per_unit' => $validated['nab_per_unit'],
            'tanggal_nab'  => $validated['tanggal'],
        ]);

        ActivityLogger::log(
            'Mengubah Data Harian',
            "Data harian ID {$hargaReksaDana->id} berhasil diperbarui",
            'success',
            $hargaReksaDana,
        );

        return redirect()->route('admin.daftar-reksa-dana.index', ['tab' => 'harian'])
            ->with('success', 'Data harian berhasil diperbarui.');
    }

    public function destroyHarian(HargaReksaDana $hargaReksaDana)
    {
        ActivityLogger::log(
            'Menghapus Data Harian',
            "Data harian ID {$hargaReksaDana->id} berhasil dihapus",
            'success',
            $hargaReksaDana,
        );

        $hargaReksaDana->delete();

        return redirect()->route('admin.daftar-reksa-dana.index', ['tab' => 'harian'])
            ->with('success', 'Data harian berhasil dihapus.');
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        $import = new \App\Imports\HargaReksaDanaImport;
        \Maatwebsite\Excel\Facades\Excel::import($import, $request->file('file'));

        $tab = $request->input('tab', 'harga');

        ActivityLogger::log(
            'Import Excel Harga',
            "Import Excel selesai: {$import->imported} diimport, {$import->skipped} dilewati",
            $import->skipped > 0 ? 'warning' : 'success',
        );

        $msg = "Import selesai. {$import->imported} data berhasil diimport.";
        if ($import->skipped > 0) {
            $msg .= " {$import->skipped} baris dilewati.";
        }

        return redirect()->route('admin.daftar-reksa-dana.index', ['tab' => $tab])
            ->with('success', $msg);
    }

    private function mergePeopleText(?string $old, string $new, InvestmentPersonService $personService): string
    {
        return collect($personService->parsePeople($old))
            ->merge($personService->parsePeople($new))
            ->unique(fn ($item) => $personService->normalizeName($item['name']))
            ->map(fn ($item) => trim($item['name'] . ($item['position'] ? ' - ' . $item['position'] : '')))
            ->values()
            ->implode("\n");
    }

    public function parseKode(Request $request)
    {
        $kode = $request->get('kode');
        if (!$kode) {
            return response()->json(['error' => 'Kode tidak boleh kosong'], 422);
        }

        $parsed = app(KodeReksaDanaParser::class)->parse($kode);

        if (empty($parsed['is_valid_length']) || empty($parsed['jenis'])) {
            $msg = 'Kode Reksa Dana tidak valid. ';
            if (strlen($kode) < 16) {
                $msg .= 'Panjang kode minimal 16 karakter.';
            } elseif (empty($parsed['nama_manajer_investasi'])) {
                $msg .= 'Kode Manajer Investasi tidak ditemukan.';
            } else {
                $msg .= 'Format kode tidak sesuai.';
            }
            return response()->json(['error' => $msg], 422);
        }

        return response()->json($parsed);
    }

    public function syncFromPasardana(Request $request)
    {
        if (empty(config('services.backend_sync.url'))) {
            $msg = 'Fitur sync Pasardana dinonaktifkan (BACKEND_SYNC_URL tidak dikonfigurasi).';
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['error' => $msg], 503);
            }
            return redirect()->route('admin.daftar-reksa-dana.index')->with('error', $msg);
        }

        $inflight = SyncRun::where('type', SyncRun::TYPE_RD_HARGA_HARIAN)
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
            return redirect()->route('admin.daftar-reksa-dana.index')
                ->with('sync_run_id', $inflight->id);
        }

        $run = SyncRun::create([
            'type' => SyncRun::TYPE_RD_HARGA_HARIAN,
            'status' => SyncRun::STATUS_QUEUED,
            'current_step' => 'queued',
            'current_step_label' => 'Menunggu worker mengambil job dari antrian',
            'progress_percent' => 0,
            'user_id' => $request->user()?->id,
        ]);

        SyncReksaDanaFromPasardanaJob::dispatch($run->id);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'run_id' => $run->id,
                'status' => $run->status,
            ]);
        }

        return redirect()->route('admin.daftar-reksa-dana.index')
            ->with('sync_run_id', $run->id)
            ->with('success', 'Sync RD + Harga Harian dari Pasardana dimulai.');
    }

    public function syncAllPasardana(Request $request)
    {
        if (empty(config('services.backend_sync.url'))) {
            $msg = 'Fitur sync Pasardana dinonaktifkan (BACKEND_SYNC_URL tidak dikonfigurasi).';
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['error' => $msg], 503);
            }
            return redirect()->route('admin.daftar-reksa-dana.index')->with('error', $msg);
        }

        $inflight = SyncRun::where('type', SyncRun::TYPE_ALL_PASARDANA)
            ->whereIn('status', [SyncRun::STATUS_QUEUED, SyncRun::STATUS_RUNNING])
            ->where('updated_at', '>=', now()->subMinutes(30))
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
            return redirect()->route('admin.daftar-reksa-dana.index')
                ->with('sync_run_id', $inflight->id);
        }

        $run = SyncRun::create([
            'type' => SyncRun::TYPE_ALL_PASARDANA,
            'status' => SyncRun::STATUS_QUEUED,
            'current_step' => 'queued',
            'current_step_label' => 'Menunggu worker mengambil job dari antrian',
            'progress_percent' => 0,
            'user_id' => $request->user()?->id,
        ]);

        SyncAllPasardanaJob::dispatch($run->id);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'run_id' => $run->id,
                'status' => $run->status,
            ]);
        }

        return redirect()->route('admin.daftar-reksa-dana.index')
            ->with('sync_run_id', $run->id)
            ->with('success', 'Sync All Pasardana dimulai.');
    }

    public function replaceRewrite(Request $request)
    {
        $inflight = SyncRun::where('type', SyncRun::TYPE_REPLACE_REWRITE)
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
            return redirect()->route('admin.daftar-reksa-dana.index')
                ->with('sync_run_id', $inflight->id);
        }

        $run = SyncRun::create([
            'type' => SyncRun::TYPE_REPLACE_REWRITE,
            'status' => SyncRun::STATUS_QUEUED,
            'current_step' => 'queued',
            'current_step_label' => 'Menunggu worker mengambil job dari antrian',
            'progress_percent' => 0,
            'user_id' => $request->user()?->id,
        ]);

        ReplaceRewriteReksaDanaJob::dispatch($run->id);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'run_id' => $run->id,
                'status' => $run->status,
            ]);
        }

        return redirect()->route('admin.daftar-reksa-dana.index')
            ->with('sync_run_id', $run->id)
            ->with('success', 'Bersihkan & Perbaiki Data dimulai.');
    }

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

    public function applySync(Request $request, SyncRun $run)
    {
        if ($run->applied_at) {
            $msg = 'Sync run ini sudah pernah di-apply.';
            return $request->wantsJson()
                ? response()->json(['error' => $msg], 422)
                : back()->with('error', $msg);
        }

        $selectedIds = $request->input('selected_ids', []);

        $query = \App\Models\SyncChangeLog::where('sync_run_id', $run->id)
            ->where('entity_type', 'rd')
            ->where('change_type', 'created')
            ->whereNotNull('pending_data');

        if (!empty($selectedIds)) {
            $query->whereIn('id', $selectedIds);
        }

        $pendingLogs = $query->get();

        if ($pendingLogs->isEmpty()) {
            return back()->with('error', 'Tidak ada data reksa dana baru yang dipilih.');
        }

        $applied = 0;
        foreach ($pendingLogs as $log) {
            $attrs = $log->pending_data;
            if (!$attrs) continue;
            ReksaDana::create($attrs);
            $log->update(['pending_data' => null]);
            $applied++;
        }

        $remainingPending = \App\Models\SyncChangeLog::where('sync_run_id', $run->id)
            ->where('entity_type', 'rd')
            ->where('change_type', 'created')
            ->whereNotNull('pending_data')
            ->count();

        // ponytail: mark applied only when all pending RDs are done
        if ($remainingPending <= 0) {
            $run->update(['applied_at' => now()]);
        }

        $msg = "{$applied} reksa dana baru berhasil ditambahkan.";
        ActivityLogger::log('Apply Sync RD', $msg, 'success');

        return $request->wantsJson()
            ? response()->json(['success' => true, 'message' => $msg, 'applied' => $applied])
            : back()->with('success', $msg);
    }

    public function parseDocument(Request $request, ProspektusParserService $parserService)
    {
        $document = ReksaDanaDocument::findOrFail($request->input('document_id'));
        $isFfs = $document->document_type === ReksaDanaDocument::TYPE_FFS;

        $rules = [
            'document_id' => 'required|exists:reksa_dana_documents,id',
        ];

        if (!$isFfs) {
            $rules['toc_start_page'] = 'required|integer|min:1';
            $rules['toc_end_page'] = 'required|integer|min:1|gte:toc_start_page';
        }

        $validated = $request->validate($rules);

        $tocStart = (int) ($validated['toc_start_page'] ?? 0);
        $tocEnd = (int) ($validated['toc_end_page'] ?? 0);

        try {
            $result = $parserService->parseDocument(
                $document,
                $tocStart,
                $tocEnd,
                $request->boolean('generate_partitions', false) && !$isFfs,
                auth()->id(),
            );

            $partitionMsg = $result['partitions_created'] > 0
                ? " {$result['partitions_created']} partisi dibuat dari daftar isi."
                : '';

            $warnings = $result['warnings'] ?? [];
            $warningMsg = !empty($warnings)
                ? ' Peringatan: ' . implode('; ', array_slice($warnings, 0, 3)) . (count($warnings) > 3 ? ' (+' . (count($warnings) - 3) . ' lainnya)' : '')
                : '';

            ActivityLogger::log(
                'Parse Dokumen',
                "Dokumen {$document->original_name} berhasil diparse ({$result['parsed_count']} halaman dari {$result['total_pages']} halaman). TOC: halaman {$result['toc_start']}-{$result['toc_end']}.{$partitionMsg}",
                'success',
                $document,
            );

            return response()->json([
                'success' => true,
                'message' => "Dokumen berhasil diparse. {$result['parsed_count']} halaman teks tersimpan.{$partitionMsg}{$warningMsg}",
                'data'    => $result,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Gagal memparse dokumen: ' . $e->getMessage()], 500);
        }
    }

    public function getDocumentParsedPages(ReksaDanaDocument $document)
    {
        $pages = $document->parsedPages()
            ->orderBy('page_parse')
            ->get(['id', 'page_pdf', 'page_parse', 'text_content']);

        return response()->json(['pages' => $pages]);
    }

    public function getDocumentPartitions(ReksaDanaDocument $document)
    {
        $partitions = $document->partitions()
            ->orderBy('start_page')
            ->get(['id', 'nama_partisi', 'start_page', 'end_page', 'start_page_pdf', 'end_page_pdf', 'source']);

        return response()->json(['partitions' => $partitions]);
    }

    public function storePartition(Request $request)
    {
        $validated = $request->validate([
            'document_id'  => 'required|exists:reksa_dana_documents,id',
            'nama_partisi' => 'required|string|max:255',
            'start_page'   => 'required|integer|min:1',
            'end_page'     => 'required|integer|min:1|gte:start_page',
        ]);

        $document = ReksaDanaDocument::findOrFail($validated['document_id']);
        $tocEnd = $this->getTocEndPageForDocument($document);

        $partition = DocumentPartition::create([
            'reksa_dana_document_id' => $validated['document_id'],
            'created_by'             => $request->user()->id,
            'nama_partisi'           => $validated['nama_partisi'],
            'start_page'             => $validated['start_page'],
            'end_page'               => $validated['end_page'],
            'start_page_pdf'         => $tocEnd !== null ? $validated['start_page'] + $tocEnd : null,
            'end_page_pdf'           => $tocEnd !== null ? $validated['end_page'] + $tocEnd : null,
            'source'                 => 'manual',
        ]);

        ActivityLogger::log(
            'Membuat Partisi',
            "Partisi '{$partition->nama_partisi}' (hlm parse {$partition->start_page}-{$partition->end_page}) dibuat",
            'success',
            $partition,
        );

        return response()->json([
            'success'   => true,
            'partition' => $partition,
        ]);
    }

    public function updatePartition(Request $request, DocumentPartition $partition)
    {
        $validated = $request->validate([
            'nama_partisi' => 'required|string|max:255',
            'start_page'   => 'required|integer|min:1',
            'end_page'     => 'required|integer|min:1|gte:start_page',
        ]);

        $tocEnd = $this->getTocEndPageForDocument($partition->document);
        $validated['start_page_pdf'] = $tocEnd !== null ? $validated['start_page'] + $tocEnd : null;
        $validated['end_page_pdf']   = $tocEnd !== null ? $validated['end_page'] + $tocEnd : null;

        $partition->update($validated);

        return response()->json([
            'success'   => true,
            'partition' => $partition->fresh(),
        ]);
    }

    private function getTocEndPageForDocument(ReksaDanaDocument $document): ?int
    {
        $firstPage = $document->parsedPages()->orderBy('page_pdf')->first();
        return $firstPage ? $firstPage->page_pdf - 1 : null;
    }

    public function destroyPartition(DocumentPartition $partition)
    {
        $partition->delete();

        return response()->json(['success' => true]);
    }

    public function extractReksaDanaData(Request $request, DocumentDataExtractorService $extractor)
    {
        $validated = $request->validate([
            'reksa_dana_id' => 'required|exists:reksa_dana,id',
            'document_id'   => 'required|exists:reksa_dana_documents,id',
            'partition_ids' => 'required|array|min:1',
            'partition_ids.*' => 'integer|exists:document_partitions,id',
        ]);

        $reksaDana = ReksaDana::findOrFail($validated['reksa_dana_id']);
        $document = ReksaDanaDocument::findOrFail($validated['document_id']);
        $partitions = DocumentPartition::where('reksa_dana_document_id', $document->id)
            ->whereIn('id', $validated['partition_ids'])
            ->orderBy('start_page')
            ->get();

        try {
            $result = $extractor->extractReksaDanaData($reksaDana, $document, $partitions->all());

            $savedFields = implode(', ', $result['saved']);
            ActivityLogger::log(
                'Parse & Simpan Data Reksa Dana',
                "Data reksa dana {$reksaDana->nama_reksa_dana} berhasil diekstrak dan disimpan. Field: {$savedFields}",
                'success',
                $reksaDana,
            );

            return response()->json([
                'success' => true,
                'message' => count($result['saved']) . ' field berhasil disimpan: ' . $savedFields,
                'data'    => $result,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function parseFfs(Request $request, ReksaDanaDocument $document, FfsExtractionService $ffsService)
    {
        try {
            $result = $ffsService->extractAndSave($document, $request->user()->id, $document->reksaDana->parser_locks ?? []);

            ActivityLogger::log(
                'Parse FFS',
                "FFS {$document->original_name} berhasil diparse dan disimpan. Data tersimpan hingga total return.",
                'success',
                $document,
            );

            return response()->json([
                'success' => true,
                'message' => 'Data FFS berhasil diekstrak dan disimpan. Data yang tersimpan di database hanya mencakup informasi hingga total return.',
                'data'    => $result,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Gagal parse FFS: ' . $e->getMessage()], 500);
        }
    }

    public function edit(ReksaDana $reksaDana)
    {
        return view('admin.daftar-reksa-dana.edit', compact('reksaDana'));
    }

    public function toggleParserLock(Request $request, ReksaDana $reksaDana)
    {
        $validated = $request->validate([
            'section' => 'required|in:info,ringkasan,risiko,biaya',
        ]);

        $locks = $reksaDana->parser_locks ?? [];
        $section = $validated['section'];

        if (in_array($section, $locks)) {
            $locks = array_values(array_filter($locks, fn($s) => $s !== $section));
        } else {
            $locks[] = $section;
        }

        $reksaDana->update(['parser_locks' => $locks]);

        return response()->json([
            'success' => true,
            'locked' => in_array($section, $locks),
            'parser_locks' => $locks,
        ]);
    }

    public function savePortfolio(Request $request, ReksaDana $reksaDana)
    {
        $validated = $request->validate([
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2000|max:2099',
            'saham' => 'nullable|numeric|min:0|max:100',
            'obligasi' => 'nullable|numeric|min:0|max:100',
            'pasar_uang' => 'nullable|numeric|min:0|max:100',
            'kas' => 'nullable|numeric|min:0|max:100',
            'top_holdings' => 'nullable|string',
            'nab_per_unit' => 'nullable|numeric',
            'tanggal_nab' => 'nullable|date',
            'aum' => 'nullable|numeric',
            'total_unit' => 'nullable|numeric',
            'return_ytd' => 'nullable|numeric',
            'return_1y' => 'nullable|numeric',
            'return_1m' => 'nullable|numeric',
            'return_inception' => 'nullable|numeric',
        ]);

        $periodDate = sprintf('%04d-%02d-01', $validated['year'], $validated['month']);

        if (array_key_exists('saham', $validated) || array_key_exists('obligasi', $validated) || array_key_exists('pasar_uang', $validated) || array_key_exists('kas', $validated)) {
            MutualFundAssetAllocation::updateOrCreate(
                ['reksa_dana_id' => $reksaDana->id, 'period_date' => $periodDate],
                [
                    'equity_percent' => $validated['saham'] ?? 0,
                    'bond_percent' => $validated['obligasi'] ?? 0,
                    'money_market_percent' => $validated['pasar_uang'] ?? 0,
                    'cash_percent' => $validated['kas'] ?? 0,
                ]
            );
        }

        if (!empty($validated['top_holdings'])) {
            MutualFundPortfolioComposition::where('reksa_dana_id', $reksaDana->id)
                ->where('period_date', $periodDate)
                ->delete();

            $lines = explode("\n", $validated['top_holdings']);
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;
                $parts = explode(':', $line);
                if (count($parts) < 2) continue;
                MutualFundPortfolioComposition::create([
                    'reksa_dana_id' => $reksaDana->id,
                    'period_date' => $periodDate,
                    'security_name' => trim($parts[0]),
                    'weight_percent' => (float) trim($parts[1]),
                    'security_type' => trim($parts[2] ?? ''),
                ]);
            }
        }

        $ringkasanFields = ['nab_per_unit', 'tanggal_nab', 'aum', 'total_unit', 'return_ytd', 'return_1y', 'return_1m', 'return_inception'];
        $ringkasanUpdates = array_intersect_key($validated, array_flip($ringkasanFields));
        if (!empty($ringkasanUpdates)) {
            $reksaDana->update($ringkasanUpdates);
        }

        if (isset($validated['nab_per_unit']) || isset($validated['aum']) || isset($validated['total_unit'])) {
            HargaReksaDana::updateOrCreate(
                ['reksa_dana_id' => $reksaDana->id, 'tanggal' => $periodDate],
                [
                    'nab_per_unit' => $validated['nab_per_unit'] ?? null,
                    'aum' => $validated['aum'] ?? null,
                    'unit_participation' => $validated['total_unit'] ?? null,
                ]
            );
        }

        $locks = $reksaDana->parser_locks ?? [];
        if (!in_array('ringkasan', $locks)) {
            $locks[] = 'ringkasan';
            $reksaDana->update(['parser_locks' => $locks]);
        }

        ActivityLogger::log(
            'Simpan Portfolio',
            "Data portfolio {$reksaDana->nama_reksa_dana} periode {$validated['month']}/{$validated['year']} berhasil disimpan.",
            'success',
            $reksaDana,
        );

        return response()->json(['success' => true, 'message' => 'Data portfolio berhasil disimpan.']);
    }

    public function updateInformasi(Request $request, ReksaDana $reksaDana)
    {
        // Decode kategori jika dikirim sebagai JSON string dari JS
        if ($request->has('kategori') && is_string($request->input('kategori'))) {
            $decoded = json_decode($request->input('kategori'), true);
            if (is_array($decoded)) {
                $request->merge(['kategori' => $decoded]);
            } elseif ($request->input('kategori') === '') {
                $request->merge(['kategori' => []]);
            }
        }

        $validated = $request->validate([
            'kode_reksa_dana'       => 'nullable|string|max:20|unique:reksa_dana,kode_reksa_dana,' . $reksaDana->id,
            'nama_reksa_dana'       => 'required|string|max:255',
            'nama_manajer_investasi'=> 'nullable|string|max:255',
            'jenis'                 => 'nullable|string|in:' . implode(',', self::JENIS_OPTIONS),
            'kategori'              => 'nullable|array',
            'kategori.*'            => 'string|in:' . implode(',', self::KATEGORI_OPTIONS),
            'kategori_produk'       => 'nullable|string|in:' . implode(',', self::KATEGORI_PRODUK_OPTIONS),
            'kelas'                 => 'nullable|string|max:10',
            'benchmark'             => 'nullable|string|max:255',
            'tujuan_investasi'      => 'nullable|string',
            'kebijakan_investasi'   => 'nullable|string',
            'custodian_bank'        => 'nullable|string|max:255',
            'launch_date'           => 'nullable|date',
            'mata_uang'             => 'nullable|string|max:10',
            'isin_code'             => 'nullable|string|max:20',
            'is_etf'                => 'nullable|boolean',
            'is_index'              => 'nullable|boolean',
            'conservative_category' => 'nullable|string|max:100',
            'dividend'              => 'nullable|boolean',
            // ringkasan
            'nab_per_unit'          => 'nullable|numeric',
            'tanggal_nab'           => 'nullable|date',
            'aum'                   => 'nullable|numeric',
            'total_unit'            => 'nullable|numeric',
            // risiko
            'risk_category'         => 'nullable|string|max:50',
            'sharpe_ratio_1y'       => 'nullable|numeric',
            'sharpe_ratio_3y'       => 'nullable|numeric',
            'sharpe_ratio_5y'       => 'nullable|numeric',
            'stdev_1y'              => 'nullable|numeric',
            'stdev_3y'              => 'nullable|numeric',
            'stdev_5y'              => 'nullable|numeric',
            'beta_1y'               => 'nullable|numeric',
            'beta_3y'               => 'nullable|numeric',
            'beta_5y'               => 'nullable|numeric',
            'max_drawdown_1y'       => 'nullable|numeric',
            'max_drawdown_3y'       => 'nullable|numeric',
            'max_drawdown_5y'       => 'nullable|numeric',
            // biaya
            'subscription_fee'      => 'nullable|numeric',
            'redemption_fee'        => 'nullable|numeric',
            'switching_fee'         => 'nullable|numeric',
            'management_fee'        => 'nullable|numeric',
            'custodian_fee'         => 'nullable|numeric',
            'expense_ratio'         => 'nullable|numeric',
            'investment_manager_fee'=> 'nullable|string|max:255',
            'minimum_subscription'  => 'nullable|numeric',
            'minimum_topup'         => 'nullable|numeric',
            'minimum_redemption'    => 'nullable|numeric',
        ]);

        if (!empty($validated['kode_reksa_dana'])) {
            $parser = app(KodeReksaDanaParser::class);
            if (!$parser->isValidKode($validated['kode_reksa_dana'])) {
                unset($validated['kode_reksa_dana']);
            } else {
                $parsedFromKode = $parser->databaseAttributes($validated['kode_reksa_dana']);
                foreach ($parsedFromKode as $key => $value) {
                    if (empty($validated[$key])) {
                        $validated[$key] = $value;
                    }
                }
            }
        }

        $validated['kategori'] = $validated['kategori'] ?? [];

        $reksaDana->update($validated);

        ActivityLogger::log(
            'Update Informasi Reksa Dana',
            "Informasi reksa dana {$reksaDana->nama_reksa_dana} berhasil diperbarui",
            'success',
            $reksaDana,
        );

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Informasi reksa dana berhasil diperbarui.']);
        }

        return redirect()->route('admin.daftar-reksa-dana.show', $reksaDana)
            ->with('success', 'Informasi reksa dana berhasil diperbarui.');
    }
}
