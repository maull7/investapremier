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
use App\Models\ReksaDana;
use App\Models\ReksaDanaDocument;
use App\Models\SyncRun;
use App\Imports\HargaReksaDanaImport;
use App\Imports\HarianReksaDanaImport;
use App\Exports\HargaReksaDanaTemplateExport;
use App\Exports\HarianReksaDanaTemplateExport;
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
use Maatwebsite\Excel\Facades\Excel;

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
        $harianSort = $request->get('harian_sort', 'reksa_dana.nama_reksa_dana');
        $harianDir = $request->get('harian_direction', 'asc');

        if ($harianTanggal) {
            $harianQuery = HargaReksaDana::with('reksaDana')
                ->join('reksa_dana', 'harga_reksa_dana.reksa_dana_id', '=', 'reksa_dana.id')
                ->where('tanggal', $harianTanggal)
                ->orderBy('reksa_dana.nama_reksa_dana', $harianDir)
                ->select('harga_reksa_dana.*');
        } else {
            $latestPerRd = HargaReksaDana::selectRaw('reksa_dana_id, MAX(tanggal) as max_tanggal')
                ->groupBy('reksa_dana_id');

            $harianQuery = HargaReksaDana::with('reksaDana')
                ->joinSub($latestPerRd, 'latest', function ($join) {
                    $join->on('harga_reksa_dana.reksa_dana_id', '=', 'latest.reksa_dana_id')
                        ->whereColumn('harga_reksa_dana.tanggal', 'latest.max_tanggal');
                })
                ->join('reksa_dana', 'harga_reksa_dana.reksa_dana_id', '=', 'reksa_dana.id')
                ->orderBy('reksa_dana.nama_reksa_dana', $harianDir)
                ->select('harga_reksa_dana.*');
        }

        if ($request->search) {
            $harianQuery->whereHas('reksaDana', fn($q) => $q->where('nama_reksa_dana', 'like', '%' . $request->search . '%'));
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
            'prospektus_year' => 'required_if:document_type,prospektus|nullable|integer|min:2000|max:2100',
            'ffs_month' => 'required_if:document_type,ffs|nullable|integer|min:1|max:12',
            'ffs_year' => 'required_if:document_type,ffs|nullable|integer|min:2000|max:2100',
            'file' => 'required|file|mimes:pdf|max:20480',
            'notes' => 'nullable|string|max:1000',
        ]);

        $file = $request->file('file');
        $filename = now()->format('Ymd-His') . '-' . Str::random(10) . '.pdf';
        $path = $file->storeAs('reksa-dana-documents/' . $validated['reksa_dana_id'], $filename, 'public');

        // Untuk prospektus, simpan tahun ke ffs_year
        if ($validated['document_type'] === 'prospektus' && !empty($validated['prospektus_year'])) {
            $validated['ffs_year'] = $validated['prospektus_year'];
        }
        unset($validated['prospektus_year']);

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
            'ffs_month' => 'required_if:document_type,ffs|nullable|integer|min:1|max:12',
            'ffs_year' => 'required|integer|min:2000|max:2100',
            'notes' => 'nullable|string|max:1000',
            'file' => 'nullable|file|mimes:pdf|max:20480',
        ]);

        if ($validated['document_type'] === 'prospektus') {
            $validated['ffs_month'] = null;
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

        // — NAV-based returns (when history is available) —
        if ($latestNav && $firstNav && $firstNav->nab_per_unit > 0) {
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

        // — Fallback to Pasardana return fields when NAV history is empty —
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

    public function uploadHarga(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv|max:5120']);

        $import = new HargaReksaDanaImport();
        Excel::import($import, $request->file('file'));

        if ($import->imported === 0) {
            $msg = 'Tidak ada data yang berhasil diimport. ';
            if ($import->skipped > 0) {
                $msg .= $import->skipped . ' baris dilewati (nama_reksa_dana tidak boleh kosong).';
            } elseif ($import->duplicates > 0) {
                $msg .= $import->duplicates . ' baris duplikat dilewati.';
            } else {
                $msg .= 'Periksa kembali format file excel anda.';
            }
            return redirect()->route('admin.daftar-reksa-dana.index', ['tab' => 'harga'])
                ->with('error', $msg);
        }

        $msg = $this->buildImportMessage('data reksa dana', $import);
        ActivityLogger::log(
            'Upload Harga Reksa Dana',
            $msg,
            'success',
        );

        return redirect()->route('admin.daftar-reksa-dana.index', ['tab' => 'harga'])
            ->with('success', $msg);
    }

    public function uploadHarian(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv|max:5120']);

        $import = new HarianReksaDanaImport();
        Excel::import($import, $request->file('file'));

        if ($import->imported === 0) {
            $msg = 'Tidak ada data yang berhasil diimport. ';
            if ($import->skipped > 0) {
                $msg .= $import->skipped . ' baris dilewati. Pastikan nama_reksa_dana, tanggal, dan nab_per_unit terisi dengan benar.';
            } elseif ($import->duplicates > 0) {
                $msg .= $import->duplicates . ' baris duplikat dilewati.';
            } else {
                $msg .= 'Periksa kembali format file excel anda.';
            }
            return redirect()->route('admin.daftar-reksa-dana.index', ['tab' => 'harian'])
                ->with('error', $msg);
        }

        $msg = $this->buildImportMessage('data harian', $import);
        ActivityLogger::log(
            'Upload Data Harian Reksa Dana',
            $msg,
            'success',
        );

        return redirect()->route('admin.daftar-reksa-dana.index', ['tab' => 'harian'])
            ->with('success', $msg);
    }

    public function downloadTemplateHarga()
    {
        return Excel::download(new HargaReksaDanaTemplateExport(), 'template-harga-reksa-dana.xlsx');
    }

    public function downloadTemplateHarian()
    {
        return Excel::download(new HarianReksaDanaTemplateExport(), 'template-harian-reksa-dana.xlsx');
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

    private function buildImportMessage(string $label, object $import): string
    {
        $msg = $import->imported . ' ' . $label . ' berhasil diupload.';

        $details = [];
        if ($import->created > 0) {
            $details[] = $import->created . ' baru';
        }
        if ($import->updated > 0) {
            $details[] = $import->updated . ' diperbarui';
        }
        if ($import->duplicates > 0) {
            $details[] = $import->duplicates . ' duplikat dilewati';
        }
        if ($import->skipped > 0) {
            $details[] = $import->skipped . ' baris kosong dilewati';
        }

        if (!empty($details)) {
            $msg .= ' (' . implode(', ', $details) . ')';
        }

        return $msg;
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

        $pendingLogs = \App\Models\SyncChangeLog::where('sync_run_id', $run->id)
            ->where('entity_type', 'rd')
            ->where('change_type', 'created')
            ->whereNotNull('pending_data')
            ->get();

        $applied = 0;
        foreach ($pendingLogs as $log) {
            $attrs = json_decode($log->pending_data, true);
            if (!$attrs) continue;
            ReksaDana::create($attrs);
            $applied++;
        }

        $run->update(['applied_at' => now()]);

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

            ActivityLogger::log(
                'Parse Dokumen',
                "Dokumen {$document->original_name} berhasil diparse ({$result['parsed_count']} halaman dari {$result['total_pages']} halaman). TOC: halaman {$result['toc_start']}-{$result['toc_end']}.{$partitionMsg}",
                'success',
                $document,
            );

            return response()->json([
                'success' => true,
                'message' => "Dokumen berhasil diparse. {$result['parsed_count']} halaman teks tersimpan.{$partitionMsg}",
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
            $result = $ffsService->extractAndSave($document, $request->user()->id);

            ActivityLogger::log(
                'Parse FFS',
                "FFS {$document->original_name} berhasil diparse dan disimpan. Field: " . implode(', ', $result['fields']),
                'success',
                $document,
            );

            return response()->json([
                'success' => true,
                'message' => 'Data FFS berhasil diekstrak dan disimpan. Field: ' . implode(', ', $result['fields']),
                'data'    => $result,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Gagal parse FFS: ' . $e->getMessage()], 500);
        }
    }
}
